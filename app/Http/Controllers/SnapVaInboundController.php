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
use stdClass;

class SnapVaInboundController extends Controller
{
    private $CURRENCY = 'IDR';
    private $INQUIRY_PROC_REASON_SUCCESS_ID = 'Sukses';
    private $INQUIRY_PROC_REASON_SUCCESS_EN = 'Success';
    private $INQUIRY_SUB_COMPANY = '00000';
    private $INQUIRY_VA_TYPE = '3';
    private $INQUIRY_INVALID_STATUS = '01';
    private $PAYMENT_RESP_STATUS_SUCCESS = '2002600';
    private $PAYMENT_MSG_SUCCESS = 'Success';
    private $PAYMENT_SUCCESS_FLAG_STATUS = '00';
    private $PAYMENT_SUCCESS_FLAG_REASON_ID = 'SUKSES';
    private $PAYMENT_SUCCESS_FLAG_REASON_EN = 'BERHASIL';
    private $ADDITIONAL_SPACE = '   ';
    private $REQUEST_TYPE = '';
    private $CLIENT = null;

    /**
     * /transfer-va/inquiry
     */
    public function transferVaInquiry(Request $request)
    {
        try {

            Log::info(">> INITIATE VA INQUIRY");
            $this->REQUEST_TYPE = 'INQUIRY';
    
            extract($request->all());

            $virtualAccountNo = trim($virtualAccountNo);
            $partnerServiceId = trim($partnerServiceId);

            $va = VirtualAccount::where('number', $virtualAccountNo)->first();
    
            $this->validateRequest(
                $request,
                [
                    'partnerServiceId' => 'required|string',
                    'customerNo' => 'required|string',
                    'virtualAccountNo' => 'required|string',
                    'trxDateInit' => 'required|string',
                    'channelCode' => 'required|numeric',
                    'inquiryRequestId' => 'required|numeric',
                ],
                $va,
                [
                    'checkConflictedExternalIdEnabled' => true,
                    'additionalData' => [
                        'partnerServiceId' => $partnerServiceId,
                        'customerNo' => $customerNo,
                        'virtualAccountNo' => $virtualAccountNo,
                        'inquiryRequestId' => $inquiryRequestId,
                    ]
                ]
            );
    
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
                'responseCode' => "2002400",
                'responseMessage' => config('app.'.$this->REQUEST_TYPE.'_VALID_VA')['MSG'],
                'virtualAccountData' => [
                    'inquiryStatus' => config('app.'.$this->REQUEST_TYPE.'_VALID_VA')['PAYMENT_FLAG_STATUS'],
                    'inquiryReason' => [
                        'english' => $this->INQUIRY_PROC_REASON_SUCCESS_EN,
                        'indonesia' => $this->INQUIRY_PROC_REASON_SUCCESS_ID,
                    ],
                    'partnerServiceId' => $this->ADDITIONAL_SPACE . $partnerServiceId,
                    'customerNo' => $customerNo,
                    'virtualAccountNo' => $this->ADDITIONAL_SPACE . $virtualAccountNo,
                    'virtualAccountName' => $va->user->name,
                    'virtualAccountEmail' => '',
                    'virtualAccountPhone' => '',
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
                    'feeAmount' => null,
                    'additionalInfo' => new stdClass,
                ],
            ];
    
            $response = response()->json($data);
    
            $this->validateResponse($response);
    
            Log::info(">> SUCCESS RESPONSE:");
            Log::info($response);
    
            return $response;
        } catch (\App\Exceptions\SnapRequestParsingException $e) {
            Log::warning($e);
            return $e->render();
        } catch(\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning($e);
            return response()->json([
                'responseCode' => '5042600',
                'responseMessage' => 'Timeout',
                'virtualAccountData' => [],
            ]);
        } catch (\Exception $e) {
            dd($e);
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

            $virtualAccountNo = trim($virtualAccountNo);
            $partnerServiceId = trim($partnerServiceId);
    
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
                    'additionalData' => [
                        'partnerServiceId' => $partnerServiceId,
                        'customerNo' => $customerNo,
                        'virtualAccountNo' => $virtualAccountNo,
                        'paymentRequestId' => $paymentRequestId,
                    ]
                ],
                'PAYMENT'
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
                    'partnerServiceId' => $this->ADDITIONAL_SPACE . $partnerServiceId,
                    'customerNo' => $customerNo,
                    'virtualAccountNo' => $this->ADDITIONAL_SPACE . $virtualAccountNo,
                    'virtualAccountName' => $va->user->name,
                    'virtualAccountEmail' => '',
                    'virtualAccountPhone' => '',
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
            Log::warning($e);
            return $e->render();
        } catch(\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning($e);
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
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_REQUEST_PARSING_ERROR');
        }
    }

    private function checkConflictedExternalId(Request $request)
    {
        $externalId = $request->headers->get('X-EXTERNAL-ID');
        $isExternalIdUnique = !Payment::where('paymentRequestId', $externalId)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if (!$isExternalIdUnique) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_CONFLICTED_EXTERNAL_ID');
        }
    }

    private function checkInconsistentExternalId(Request $request)
    {
        $externalId = $request->headers->get('X-EXTERNAL-ID');
        $isExternalIdUnique = !Payment::where('paymentRequestId', $externalId)
            ->whereDate('created_at', Carbon::today())
            ->first();

        if ($isExternalIdUnique) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_INCONSISTENT_EXTERNAL_ID');
        }
    }

    private function checkIsVaSettled($virtualAccount, $data)
    {
        $isVaSettled = $virtualAccount->outstanding === '0';

        if ($isVaSettled) {
            $virtualAccountData = [
                'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                'inquiryReason' => [
                    'english' => 'Already paid',
                    'indonesia' => 'Tagihan sudah dibayar',
                ],
                'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                'customerNo' => $data['customerNo'],
                'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                'virtualAccountName' => '',
                'virtualAccountEmail' => '',
                'virtualAccountPhone' => '',
                'inquiryRequestId' => $data['inquiryRequestId'],
                'totalAmount' => [
                    'value' => $virtualAccount->outstanding,
                    'currency' => $this->CURRENCY,
                ],
                'subCompany' => '',
                'billDetails' => [],
                'freeTexts' => [
                    [
                        'english' => '',
                        'indonesia' => '',
                    ],
                ],
                'virtualAccountTrxType' => $this->INQUIRY_VA_TYPE,
                'feeAmount' => null,
                'additionalInfo' => new stdClass,
            ];
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_VALID_VA_SETTLED', '', $virtualAccountData);
        }
    }

    private function checkIsVaRegistered($virtualAccount, $data)
    {
        if (!$virtualAccount) {
            $virtualAccountData = [
                'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                'inquiryReason' => [
                    'english' => 'Bill not found',
                    'indonesia' => 'Tagihan tidak ditemukan',
                ],
                'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                'customerNo' => $data['customerNo'],
                'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                'virtualAccountName' => '',
                'virtualAccountEmail' => '',
                'virtualAccountPhone' => '',
                'inquiryRequestId' => $data['inquiryRequestId'],
                'totalAmount' => [
                    'value' => '',
                    'currency' => '',
                ],
                'subCompany' => '',
                'billDetails' => [],
                'freeTexts' => [
                    [
                        'english' => '',
                        'indonesia' => '',
                    ],
                ],
                'virtualAccountTrxType' => '',
                'feeAmount' => null,
                'additionalInfo' => new stdClass,
            ];

            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_UNREGISTERED_VA', '', $virtualAccountData);
        }
    }

    private function checkIsVaExpired($virtualAccount, $data)
    {
        $isVaExpired = !$virtualAccount->is_active;

        if ($isVaExpired) {
            $virtualAccountData = [
                'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                'inquiryReason' => [
                    'english' => 'Bill expired',
                    'indonesia' => 'Tagihan kedaluarsa',
                ],
                'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                'customerNo' => $data['customerNo'],
                'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                'virtualAccountName' => '',
                'virtualAccountEmail' => '',
                'virtualAccountPhone' => '',
                'inquiryRequestId' => $data['inquiryRequestId'],
                'totalAmount' => [
                    'value' => $virtualAccount->outstanding,
                    'currency' => $this->CURRENCY,
                ],
                'subCompany' => '',
                'billDetails' => [],
                'freeTexts' => [
                    [
                        'english' => '',
                        'indonesia' => '',
                    ],
                ],
                'virtualAccountTrxType' => '',
                'feeAmount' => null,
                'additionalInfo' => new stdClass,
            ];

            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_VALID_VA_EXPIRED', '', $virtualAccountData);
        }
    }

    private function validateRequest(
        Request $request,
        $validation,
        $virtualAccount,
        $options = [
            'checkConflictedExternalIdEnabled' => true,
            'additionalData' => []
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
        $this->checkIsVaRegistered($virtualAccount, $options['additionalData']);
        
        // Check is VA settled
        $this->checkIsVaSettled($virtualAccount, $options['additionalData']);

        // Check is VA expired
        $this->checkIsVaExpired($virtualAccount, $options['additionalData']);

        // Check is token valid
        $this->checkIsTokenValid($request);

        // Check valid signature 
        $this->checkIsSignatureValid($request);
    }

    private function checkIsSignatureValid(Request $request)
    {
        $httpMethod = $request->method();
        $relativeUrl = '/' . $request->path();
        $accessToken = $request->bearerToken();
        $requestBody = $request->json()->all();
        $hashedMinifiedJsonBody = hash(
            "sha256",
            json_encode( // minify and remove whitespace
                $requestBody,
                JSON_UNESCAPED_SLASHES
            )
        );
        Log::info($requestBody);
        Log::info(json_encode( // minify and remove whitespace
            $requestBody,
            JSON_UNESCAPED_SLASHES
        ));
        Log::info($hashedMinifiedJsonBody);
        $signature = $request->header('X-SIGNATURE');
        $timestampStr = $request->header('X-TIMESTAMP');
        $stringToSign = "$httpMethod:$relativeUrl:$accessToken:$hashedMinifiedJsonBody:$timestampStr";
        Log::info($stringToSign);
        $bcaSecret = $this->CLIENT->secret;
        $signatureTester = base64_encode(
            hash_hmac(
                "sha512",
                $stringToSign,
                $bcaSecret,
                true
            )
        );
        Log::info($signature);
        Log::info($signatureTester);
        if ($signatureTester !== $signature) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_UNAUTHORIZED_SIGNATURE');
        }
    }

    private function checkIsTokenValid(Request $request)
    {
        $clientId = $request->headers->get('X-CLIENT-KEY');
        $client = null;
        
        if (!$clientId) {
            $clientId = $request->headers->get('X-PARTNER-ID');
            $client = OAuthClient::where('partner_id', $clientId)->first();
            $this->CLIENT = $client;
        } else {
            $client = OAuthClient::find($clientId);
        }

        if (!$client) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_UNAUTHORIZED_UNKNOWN_CLIENT');
        }

        $authorization = $request->bearerToken();
        $validated = Token::validate($authorization, $client->secret);

        if (!$validated) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_ACCESS_TOKEN_INVALID');
        }
    }

    private function validateResponse($response)
    {
        json_decode($response);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_RESPONSE_PARSING_ERROR');
        }
    }

    private function checkInvalidHeaderFieldFormats(Request $request)
    {
        if ($request->has("grantType")) {
            $hasValidGrantValue = $request->get('grantType') === 'client_credentials';

            if (!$hasValidGrantValue) {
                throw new SnapRequestParsingException($this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD');
            }

            $isClientKeyExist = $request->headers->has('X-CLIENT-KEY');

            if (!$isClientKeyExist) {
                throw new SnapRequestParsingException($this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD');
            }
        } else {
            $hasValidHeadersValue = $request->headers->has('X-EXTERNAL-ID');

            if (!$hasValidHeadersValue) {
                throw new SnapRequestParsingException($this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD');
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
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_MISSING_MANDATORY_FIELD');
        }

        // Check mandatory body
        $bodyValidator = Validator::make($request->all(), $validation);

        if ($bodyValidator->fails()) {
            $messages = $bodyValidator->getMessageBag();
            $failedAttributes = array_keys($messages->getMessages());
            $additionalMessage = implode(', ', $failedAttributes);
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_MISSING_MANDATORY_FIELD', $additionalMessage);
        }
    }
}
