<?php

namespace App\Http\Controllers;

use App\Exceptions\SnapRequestParsingException;
use App\Models\Payment;
use App\Models\VirtualAccount;
use App\Models\OAuthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use ReallySimpleJWT\Token;

class SnapVaInboundController extends Controller
{
    private $CURRENCY = 'IDR';
    private $INQUIRY_PROC_REASON_SUCCESS_ID = 'Sukses';
    private $INQUIRY_PROC_REASON_SUCCESS_EN = 'Success';
    private $INQUIRY_SUB_COMPANY = '00000';
    private $INQUIRY_VA_TYPE = '2'; // Open Payment (O): Tagihan tidak muncul (No Bill)
    private $PAYMENT_RESP_STATUS_SUCCESS = '2002600';
    private $PAYMENT_MSG_SUCCESS = 'Success';
    private $PAYMENT_SUCCESS_FLAG_STATUS = '00';
    private $PAYMENT_SUCCESS_FLAG_REASON_ID = 'SUKSES';
    private $PAYMENT_SUCCESS_FLAG_REASON_EN = 'BERHASIL';

    /**
     * /access-token/b2b
     */
    public function generateAccessTokenB2b(Request $request)
    {
        $this->validateRequest($request, [], null);

        return response()->json($request->all());
    }

    /**
     * /transfer-va/inquiry
     */
    public function transferVaInquiry(Request $request)
    {
        try {

            Log::info(">> INITIATE VA INQUIRY");
    
            extract($request->all());
    
            $va = VirtualAccount::where('number', $virtualAccountNo)->first();
    
            $this->validateRequest($request, [
                'partnerServiceId' => 'required|string',
                'customerNo' => 'required|string',
                'virtualAccountNo' => 'required|string',
                'trxDateInit' => 'required|string',
                'channelCode' => 'required|numeric',
                'inquiryRequestId' => 'required|numeric',
            ], $va);
    
    
            if (!$va) {
                throw new SnapRequestParsingException('VALID_VA_UNREGISTERED');
            }
    
            // Scenario: bill already paid
            if ($va->outstanding === '0') {
                throw new SnapRequestParsingException('VALID_VA_SETTLED');
            }
            
            // Scenario: bill expired
            if (!$va->is_active) {
                throw new SnapRequestParsingException('VALID_VA_EXPIRED');
            }
    
            // Create payment instance
            $externalId = $request->headers->get('X-EXTERNAL-ID');
    
            Payment::create([
                'partnerServiceId' => $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNumber' => $virtualAccountNo,
                'virtualAccountName' => $va->user->name,
                'trxId' => '',
                'paymentRequestId' => $externalId,
                'channelCode' => $channelCode,
                'paidAmount' => json_encode([
                    'value' => $va->outstanding,
                    'currency' => $this->CURRENCY,
                ])
            ]);
    
            $data = [
                'responseCode' => config('app.VALID_VA')['CODE'],
                'responseMessage' => config('app.VALID_VA')['MSG'],
                'virtualAccountData' => [
                    'inquiryStatus' => config('app.VALID_VA')['PAYMENT_FLAG_STATUS'],
                    'inquiryReason' => [
                        'english' => $this->INQUIRY_PROC_REASON_SUCCESS_EN,
                        'indonesia' => $this->INQUIRY_PROC_REASON_SUCCESS_ID,
                    ],
                    'partnerServiceId' => $partnerServiceId,
                    'customerNo' => $customerNo,
                    'virtualAccountNo' => $virtualAccountNo,
                    'virtualAccountName' => $va->user->name,
                    'inquiryRequestId' => $inquiryRequestId,
                    'totalAmount' => [
                        'value' => $va->outstanding,
                        'currency' => $this->CURRENCY,
                    ],
                    'subCompany' => $this->INQUIRY_SUB_COMPANY,
                    'billDetails' => [
                        [
                            'billNo' => $virtualAccountNo,
                            'billDescription' => [
                                'english' => $va->description,
                                'indonesia' => $va->description,
                            ],
                            'billSubCompany' => $this->INQUIRY_SUB_COMPANY,
                            'billAmount' => [
                                'value' => $va->outstanding,
                                'currency' => $this->CURRENCY,
                            ],
                        ],
                    ],
                    'freeTexts' => [
                        [
                            'english' => $va->description,
                            'indonesia' => $va->description,
                        ],
                    ],
                    'virtualAccountTrxType' => $this->INQUIRY_VA_TYPE,
                    'feeAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'additionalInfo' => '',
                ],
            ];
    
            $response = response()->json($data);
    
            $this->validateResponse($response);
    
            Log::info(">> SUCCESS RESPONSE:");
            Log::info($response);
    
            return $response;
        } catch (\App\Exceptions\SnapRequestParsingException $e) {
            return $e->render();
        } catch(\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'responseCode' => '5042600',
                'responseMessage' => 'Timeout',
                'virtualAccountData' => [],
            ]);
        } catch (\Exception $e) {
            Log::info("INTERNAL SERVER ERROR");
            Log::warning($e);

            throw new SnapRequestParsingException('SERVER_INTERNAL_ERROR');
        }
    }

    /**
     * /transfer-va/payment
     */
    public function transferVaPayment(Request $request)
    {
        try {

            Log::info(">> INITIATE VA TRANSFER");
    
            extract($request->all());
    
            $va = VirtualAccount::where('number', $virtualAccountNo)->first();
    
            $this->validateRequest(
                $request,
                [
                    'partnerServiceId' => 'required|string',
                    'customerNo' => 'required|string',
                    'virtualAccountNo' => 'required|string',
                    'virtualAccountName' => 'required|string',
                    'paymentRequestId' => 'required|string',
                    'channelCode' => 'required|numeric',
                    'paidAmount.value' => 'required|string',
                    'paidAmount.currency' => 'required|string',
                    'flagAdvise' => 'required|string',
                ],
                $va,
                [
                    'checkConflictedExternalIdEnabled' => false,
                ]
            );

            $newOutstanding = $va->outstanding - $request->get('paidAmount')['value'];

            if ($newOutstanding < 0) {
                throw new SnapRequestParsingException('SERVER_INTERNAL_ERROR');
            }

            $va->update([ 'outstanding' => $newOutstanding ]);
    
            $data = [
                'responseCode' => $this->PAYMENT_RESP_STATUS_SUCCESS,
                'responseMessage' => $this->PAYMENT_MSG_SUCCESS,
                'virtualAccountData' => [
                    'paymentFlagStatus' => $this->PAYMENT_SUCCESS_FLAG_STATUS,
                    'paymentFlagReason' => [
                        'indonesia' => $this->PAYMENT_SUCCESS_FLAG_REASON_ID,
                        'english' => $this->PAYMENT_SUCCESS_FLAG_REASON_EN,
                    ],
                    'partnerServiceId' => $partnerServiceId,
                    'customerNo' => $customerNo,
                    'virtualAccountNo' => $virtualAccountNo,
                    'virtualAccountName' => $va->user->name,
                    'inquiryRequestId' => '202202110909314440200001136962',
                    'paymentRequestId' => '202202110909314440200001136962',
                    'paidAmount' => [
                        'value' => $paidAmount['value'],
                        'currency' => $paidAmount['currency'],
                    ],
                    'totalAmount' => [
                        'value' => $totalAmount['value'],
                        'currency' => $totalAmount['value'],
                    ],
                    'transactionDate' => $trxDateTime,
                    'referenceNo' => $billDetails[0]['billReferenceNo'],
                    'billDetails' => [
                        [
                            'billNo' => $billDetails[0]['billNo'],
                            'billDescription' => [
                                'english' => $billDetails[0]['billDescription']['english'],
                                'indonesia' => $billDetails[0]['billDescription']['indonesia'],
                            ],
                            'billSubCompany' => $billDetails[0]['billSubCompany'],
                            'billAmount' => [
                                'value' => $billDetails[0]['billAmount']['value'],
                                'currency' => $billDetails[0]['billAmount']['currency'],
                            ],
                            'additionalInfo' => [
                                'value' => $billDetails[0]['additionalInfo']['value'],
                            ],
                            'billReferenceNo' => $billDetails[0]['billReferenceNo'],
                            'status' => $this->PAYMENT_SUCCESS_FLAG_STATUS,
                            'reason' => [
                                'english' => $billDetails[0] && isset($billDetails[0]['reason']) ? $billDetails[0]['reason']['english'] : '',
                                'indonesia' => $billDetails[0] && isset($billDetails[0]['reason']) ? $billDetails[0]['reason']['indonesia'] : '',
                            ],
                        ]
                    ],
                ],
            ];
    
            $response = response()->json($data);
    
            $this->validateResponse($response);
    
            Log::info(">> SUCCESS RESPONSE:");
            Log::info($response);
    
            return $response;
        } catch (\App\Exceptions\SnapRequestParsingException $e) {
            return $e->render();
        } catch(\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'responseCode' => '5042600',
                'responseMessage' => 'Timeout',
                'virtualAccountData' => [],
            ]);
        } catch (\Exception $e) {
            Log::info("INTERNAL SERVER ERROR");
            Log::warning($e);

            throw new SnapRequestParsingException('SERVER_INTERNAL_ERROR');
        }
    }

    /**
     * ======================[ VALIDATIONS ]======================
     */
    
    private function checkRequestParsingError(Request $request)
    {
        json_decode($request->getContent());

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new SnapRequestParsingException('REQUEST_PARSING_ERROR');
        }
    }

    private function checkConflictedExternalId(Request $request)
    {
        $externalId = $request->headers->get('X-EXTERNAL-ID');
        $isExternalIdUnique = !Payment::where('paymentRequestId', $externalId)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$isExternalIdUnique) {
            throw new SnapRequestParsingException('CONFLICTED_EXTERNAL_ID');
        }
    }

    private function checkInconsistentExternalId(Request $request)
    {
        $externalId = $request->headers->get('X-EXTERNAL-ID');
        $isExternalIdUnique = !Payment::where('paymentRequestId', $externalId)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($isExternalIdUnique) {
            throw new SnapRequestParsingException('INCONSISTENT_EXTERNAL_ID');
        }
    }

    private function checkIsVaSettled($virtualAccount)
    {
        $isVaSettled = $virtualAccount->outstanding === '0';

        if ($isVaSettled) {
            throw new SnapRequestParsingException('VALID_VA_SETTLED');
        }
    }

    private function checkIsVaRegistered($virtualAccount)
    {
        if (!$virtualAccount) {
            throw new SnapRequestParsingException('VALID_VA_UNREGISTERED');
        }
    }

    private function checkIsVaExpired($virtualAccount)
    {
        $isVaExpired = !$virtualAccount->is_active;

        if ($isVaExpired) {
            throw new SnapRequestParsingException('VALID_VA_EXPIRED');
        }
    }

    private function validateRequest(
        Request $request,
        $validation,
        $virtualAccount,
        $options = [
            'checkConflictedExternalIdEnabled' => true,
        ]
    ) {
        // Check request parsing error
        $this->checkRequestParsingError($request);

        // Check mandatory fields
        $this->checkMandatoryFields($request, $validation);

        // Check invalid field format
        $this->checkInvalidHeaderFieldFormats($request);

        // Check is External ID conflicted
        if ($options['checkConflictedExternalIdEnabled']) {
            $this->checkConflictedExternalId($request);
        } else {
            $this->checkInconsistentExternalId($request);
        }

        // Check is VA registered
        $this->checkIsVaRegistered($virtualAccount);

        // Check is VA settled
        $this->checkIsVaSettled($virtualAccount);

        // Check is VA expired
        $this->checkIsVaExpired($virtualAccount);

        // Check is token valid
        $this->checkIsTokenValid($request);
    }

    private function checkIsTokenValid(Request $request)
    {
        $clientId = $request->headers->get('X-CLIENT-KEY');
        $client = null;
        if (!$clientId) {
            $clientId = $request->headers->get('X-PARTNER-ID');
            $client = OAuthClient::where('partner_id', $clientId)->first();
        } else {
            $client = OAuthClient::find($clientId);
        }
        $authorization = $request->bearerToken();
        $validated = Token::validate($authorization, $client->secret);

        if (!$validated) {
            throw new SnapRequestParsingException('ACCESS_TOKEN_INVALID');
        }
    }

    private function validateResponse($response)
    {
        json_decode($response);
        return json_last_error() === JSON_ERROR_NONE;
    }

    private function checkInvalidHeaderFieldFormats(Request $request)
    {
        if ($request->has("grantType")) {
            $hasValidGrantValue = $request->get('grantType') === 'client_credentials';

            if (!$hasValidGrantValue) {
                throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
            }

            $isClientKeyExist = $request->headers->has('X-CLIENT-KEY');

            if (!$isClientKeyExist) {
                throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
            }
        } else {
            $hasValidHeadersValue = $request->headers->has('X-EXTERNAL-ID');

            if (!$hasValidHeadersValue) {
                throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
            }
        }
    }

    /**
     * Mandatory headers:
     * - CHANNEL-ID
     * - X-PARTNER-ID
     * 
     * Mandatory body:
     * - partnerServiceId
     * - customerNo
     * - virtualAccountNo
     * - virtualAccountName
     * - paymentRequestId
     * - channelCode
     * - paidAmount
     *  - value
     *  - currency
     * - flagAdvise
     */
    private function checkMandatoryFields(Request $request, $validation)
    {
        // Check mandatory headers
        $isHeadersValid = $request->headers->has('CHANNEL-ID') && $request->headers->has('X-PARTNER-ID');

        if (!$isHeadersValid) {
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
        }

        // Check mandatory body
        $bodyValidator = Validator::make($request->all(), $validation);

        if ($bodyValidator->fails()) {
            $messages = $bodyValidator->getMessageBag();
            $failedAttributes = array_keys($messages->getMessages());
            $additionalMessage = implode(', ', $failedAttributes);
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD', $additionalMessage);
        }
    }
}
