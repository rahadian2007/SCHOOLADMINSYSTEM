<?php

namespace App\Http\Controllers;

use App\Exceptions\SnapRequestParsingException;
use App\Models\Payment;
use App\Models\VirtualAccount;
use App\Models\OAuthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
    private $PAYMENT_RESP_STATUS_SUCCESS = '2002500';
    private $PAYMENT_MSG_SUCCESS = 'Success';
    private $PAYMENT_SUCCESS_FLAG_STATUS = '00';
    private $PAYMENT_INVALID_STATUS = '01';
    private $PAYMENT_SUCCESS_FLAG_REASON_ID = 'Sukses';
    private $PAYMENT_SUCCESS_FLAG_REASON_EN = 'Success';
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

            Log::info(">> headers");
            Log::notice($request->header());
    
            $virtualAccountNo = trim(isset($virtualAccountNo) ? $virtualAccountNo : $request->input('virtualAccountNo'));
            $partnerServiceId = trim(isset($partnerServiceId) ? $partnerServiceId : $request->input('partnerServiceId'));
            $customerNo = $request->input('customerNo');
            $inquiryRequestId = $request->input('inquiryRequestId');

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
            $channelCode = $request->headers->get('CHANNEL-ID');
    
            Log::info('Creating payment record');

            Payment::create([
                'partnerServiceId' => $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNumber' => $virtualAccountNo,
                'virtualAccountName' => $va->user->name,
                'trxId' => '',
                'paymentRequestId' => $inquiryRequestId,
                'externalId' => $externalId,
                'channelCode' => $channelCode,
                'paidAmount' => json_encode([
                    'value' => $va->outstanding,
                    'currency' => $this->CURRENCY,
                ])
            ]);

            Log::info('Finished creating payment');
            Log::info('Construct response data');
    
            $data = [
                'responseCode' => "2002400",
                'responseMessage' => config('app.'.$this->REQUEST_TYPE.'_VALID_VA')['MSG'],
                'virtualAccountData' => $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => config('app.'.$this->REQUEST_TYPE.'_VALID_VA')['PAYMENT_FLAG_STATUS'],
                    'inquiryReason' => [
                        'english' => $this->INQUIRY_PROC_REASON_SUCCESS_EN,
                        'indonesia' => $this->INQUIRY_PROC_REASON_SUCCESS_ID,
                    ]
                ]),
            ];
    
            $response = response()->json($data);
    
            $this->validateResponse($response);
    
            Log::info(">> SUCCESS RESPONSE:");
            Log::notice($response);
    
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
     * /transfer-va/payment
     */
    public function transferVaPayment(Request $request)
    {
        try {
            Log::info(">> INITIATE VA PAYMENT");

            $this->REQUEST_TYPE = 'PAYMENT';

            Log::info(">> Headers");
            Log::notice($request->header());
    
            $virtualAccountNo = trim(isset($virtualAccountNo) ? $virtualAccountNo : $request->input('virtualAccountNo'));
            $partnerServiceId = trim(isset($partnerServiceId) ? $partnerServiceId : $request->input('partnerServiceId'));
            $customerNo = $request->input('customerNo');
            $paymentRequestId = $request->input('paymentRequestId');
            $paidAmount = $request->input('paidAmount');
            $trxDateTime = $request->input('trxDateTime');
            $referenceNo = $request->input('referenceNo');
    
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
                    'additionalData' => [
                        'partnerServiceId' => $partnerServiceId,
                        'customerNo' => $customerNo,
                        'virtualAccountNo' => $virtualAccountNo,
                        'paymentRequestId' => $paymentRequestId,
                        'trxDateTime' => $trxDateTime,
                        'paidAmount' => $paidAmount,
                        'referenceNo' => $referenceNo,
                    ]
                ]
            );

            $actualPaidAmount = $request->get('paidAmount'); 
            $paidAmountValue = $actualPaidAmount['value'];
            $newOutstanding = $va->outstanding - $paidAmountValue;

            Log::info(">> New outstanding");
            Log::notice($newOutstanding);

            if ($newOutstanding < 0) {
                throw new SnapRequestParsingException('SERVER_INTERNAL_ERROR');
            }

            Log::info(">> Updating VA");

            $vaUpdatedData = [ 'outstanding' => $newOutstanding ];
            $billDetails = [];

            Log::info(">> Substract on bill components");

            $vaUpdatedData['description'] = $this->substractBillComponents($va, $paidAmountValue);
            $va->update($vaUpdatedData);

            Log::info('>> New VA Data');
            Log::notice($va);

            $payment = Payment::where('paymentRequestId', $paymentRequestId)
                ->where('externalId', $request->headers->get('X-EXTERNAL-ID'))
                ->where('paymentFlagStatus', $this->PAYMENT_INVALID_STATUS)
                ->first();

            $payment->update([
                'paymentFlagStatus' => $this->PAYMENT_SUCCESS_FLAG_STATUS,
                'paidAmount' => json_encode($actualPaidAmount),
            ]);

            Log::info('>> New Payment Data');
            Log::notice($payment);
    
            $data = [
                'responseCode' => $this->PAYMENT_RESP_STATUS_SUCCESS,
                'responseMessage' => $this->PAYMENT_MSG_SUCCESS,
                'virtualAccountData' => $this->buildVaResponsePayload($request, [
                    'paymentFlagReason' => [
                        'english' => $this->PAYMENT_SUCCESS_FLAG_REASON_EN,
                        'indonesia' => $this->PAYMENT_SUCCESS_FLAG_REASON_ID,
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_SUCCESS_FLAG_STATUS,
                ]),
                'additionalInfo' => new stdClass,
            ];
    
            $response = response()->json($data);
    
            $this->validateResponse($response);
    
            Log::info(">> SUCCESS RESPONSE:");
            Log::notice($response);
    
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
     * Assumption: paid amount is less than or equal to bill amount
     * $va: VirtualAccount = Virtual Account from database
     * $paidAmount: number = Actual paid amount by user
     */
    private function substractBillComponents($va, $paidAmount) {
        if (!$va || !$paidAmount) {
            return null;
        }

        $billComponents = json_decode($va->description);

        if (!is_array($billComponents)) {
            return null;
        }

        $remainder = $paidAmount;
        $newBillComponents = [];
        foreach ($billComponents as $component) {
            if ($component->value <= $remainder) {
                $newBillComponents[$component->name] = 0;
                $remainder -= $component->value;
            } else {
                $newBillComponents[$component->name] = $component->value - $remainder;
                $remainder = 0;
            }
        }

        return json_encode($newBillComponents);
    }

    /**
     * ======================[ VALIDATIONS ]======================
     */

    private function buildVaResponsePayload($request, $payload = [
        'inquiryStatus' => '',
        'inquiryReason' => [
            'english' => '',
            'indonesia' => '',
        ],
        'paymentFlagReason' => [
            'english' => '',
            'indonesia' => '',
        ],
        'paymentFlagStatus' => '',
    ])
    {
        $virtualAccountNo = $request->input('virtualAccountNo') ? trim($request->input('virtualAccountNo')) : '';
        $partnerServiceId = $request->input('partnerServiceId') ? trim($request->input('partnerServiceId')) : '';
        $customerNo = $request->input('customerNo');
        $paidAmount = $request->input('paidAmount');
        $trxDateTime = $request->input('trxDateTime');
        $referenceNo = $request->input('referenceNo');
        $totalAmount = $request->input('totalAmount');
        $billDetails = $request->input('billDetails');

        $va = VirtualAccount::where('number', $virtualAccountNo)->first();

        if ($this->REQUEST_TYPE === 'INQUIRY') {
            $inquiryRequestId = $request->input('inquiryRequestId');

            return [
                'inquiryStatus' => $payload['inquiryStatus'],
                'inquiryReason' => [
                    'english' => $payload['inquiryReason']['english'],
                    'indonesia' => $payload['inquiryReason']['indonesia'],
                ],
                'partnerServiceId' => $this->ADDITIONAL_SPACE . $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNo' => $this->ADDITIONAL_SPACE . $virtualAccountNo,
                'virtualAccountName' => $va && $va->user ? $va->user->name : '',
                'virtualAccountEmail' => '',
                'virtualAccountPhone' => '',
                'inquiryRequestId' => $inquiryRequestId,
                'totalAmount' => [
                    'value' => $va ? $va->outstanding : '',
                    'currency' => $this->CURRENCY,
                ],
                'subCompany' => $this->INQUIRY_SUB_COMPANY,
                'billDetails' => $this->constructBillDetails($va),
                'freeTexts' => [
                    [
                        'english' => '',
                        'indonesia' => '',
                    ],
                ],
                'virtualAccountTrxType' => $this->INQUIRY_VA_TYPE,
                'feeAmount' => null,
                'additionalInfo' => new stdClass(),
            ];
        } else if ($this->REQUEST_TYPE === 'PAYMENT') {
            $paymentRequestId = $request->input('paymentRequestId');
            $data = [
                'paymentFlagReason' => [
                    'english' => $payload['paymentFlagReason']['english'],
                    'indonesia' => $payload['paymentFlagReason']['indonesia'],
                ],
                'partnerServiceId' => $this->ADDITIONAL_SPACE . $partnerServiceId,
                'customerNo' => $customerNo,
                'virtualAccountNo' => $this->ADDITIONAL_SPACE . $virtualAccountNo,
                'virtualAccountName' => $va && $va->user ? $va->user->name : '',
                'virtualAccountEmail' => '',
                'virtualAccountPhone' => '',
                'trxId' => '',
                'paymentRequestId' => $paymentRequestId,
                'paidAmount' => [
                    'value' => $paidAmount['value'],
                    'currency' => $paidAmount['currency'],
                ],
                'paidBills' => '',
                'totalAmount' => [
                    'value' => $totalAmount['value'],
                    'currency' => $totalAmount['currency'],
                ],
                'trxDateTime' => $trxDateTime ? $trxDateTime : '',
                'referenceNo' => $referenceNo,
                'journalNum' => '',
                'paymentType' => '',
                'flagAdvise' => 'N',
                'paymentFlagStatus' => $payload['paymentFlagStatus'],
                'billDetails' => [],
                'freeTexts' => [],
            ];

            if (sizeof($billDetails) > 0 && isset($data['virtualAccountData']) && isset($billDetails[0])) {
                $data['virtualAccountData']['billReferenceNo'] = $billDetails[0]['billReferenceNo'];
                $data['virtualAccountData']['billDetails'] = [
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
                        'status' => $payload['paymentFlagStatus'],
                        'reason' => [
                            'english' => $billDetails[0] && isset($billDetails[0]['reason']) ? $billDetails[0]['reason']['english'] : '',
                            'indonesia' => $billDetails[0] && isset($billDetails[0]['reason']) ? $billDetails[0]['reason']['indonesia'] : '',
                        ],
                    ]
                ];
            }

            return $data;
        }
    }

    private function checkPaymentInvalidAmount(Request $request)
    {
        $paidAmount = $request->get('paidAmount');
        $totalAmount = $request->get('totalAmount');
        $invalidPaidAmount = !$paidAmount || !isset($paidAmount['value']) || !isset($paidAmount['currency']) || !is_numeric($paidAmount['value']);
        $invalidTotalAmount = !$totalAmount || !isset($totalAmount['value']) || !isset($totalAmount['currency']) || !is_numeric($totalAmount['value']);

        if ($invalidPaidAmount || $invalidTotalAmount) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_AMOUNT',
                '',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Invalid amount',
                        'indonesia' => 'Jumlah tidak valid',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Invalid amount',
                        'indonesia' => 'Jumlah tidak valid',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        $paymentRequestId = $request->get('paymentRequestId');
        $externalId = $request->get('externalId') ? $request->get('externalId') : $request->headers->get('X-EXTERNAL-ID');
        
        Log::info(">> externalId");
        Log::notice($externalId);

        $today = DB::raw('CURDATE()');
        $payment = Payment::where('paymentRequestId', $paymentRequestId)
                ->where('externalId', $externalId)
                ->whereDate('created_at', $today)
                ->first();

        if (!$payment) {
            $externalId = $request->headers->get('X-EXTERNAL-ID');
            $payment = Payment::where('externalId', $externalId)
                ->whereDate('created_at', $today)
                ->first();
        }

        if (!$payment) {
            $virtualAccountNo = $request->get('virtualAccountNo') ? trim($request->get('virtualAccountNo')) : '';
            $va = VirtualAccount::where('number', $virtualAccountNo)->first();
            $this->checkInconsistentExternalId($request, $va);
            $payment = Payment::where('paymentRequestId', $paymentRequestId)
                ->where('externalId', $externalId)
                ->first();
        }

        Log::info(">> payment");
        Log::notice($payment);

        if (!$payment) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_VALID_VA_EXPIRED',
                '',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Invalid bill',
                        'indonesia' => 'Tagihan tidak valid',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Invalid bill',
                        'indonesia' => 'Tagihan tidak valid',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        $dbPaidAmount = json_decode($payment->paidAmount);
        $dbPaidAmountInt = $dbPaidAmount->value ? intval($dbPaidAmount->value) : null;
        $paidAmountInt = $paidAmount['value'] ? intval($paidAmount['value']) : null;
        
        $isInconsistentPaidAmount =
            !$dbPaidAmountInt   // No paid amount recorded
            || !$paidAmountInt  // No paid amount requested
            || (
                $dbPaidAmountInt
                && $paidAmountInt
                && $dbPaidAmountInt < $paidAmountInt // User paid more from bill
            );

        if ($isInconsistentPaidAmount) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_AMOUNT',
                '',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Inconsistent amount',
                        'indonesia' => 'Jumlah tidak konsisten',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Inconsistent amount',
                        'indonesia' => 'Jumlah tidak konsisten',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }
        
    }

    private function checkRequestParsingError(Request $request)
    {
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_REQUEST_PARSING_ERROR');
        }
    }

    private function checkConflictedExternalId(Request $request)
    {
        $externalId = $request->headers->get('X-EXTERNAL-ID');
        $payment = Payment::where('externalId', $externalId)
            ->whereDate('created_at', DB::raw('CURDATE()'))
            ->first();

        if ($this->REQUEST_TYPE === 'PAYMENT') {
            $isConflicted = $payment && $payment->paymentRequestId !== $request->get('paymentRequestId');
            if ($isConflicted) {
                Log::warning('>>> $payment->externalId');
                Log::warning($payment->externalId);
                Log::warning('>>> paymentRequestId');
                Log::warning($request->get('paymentRequestId'));
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_CONFLICTED_EXTERNAL_ID',
                    '',
                    $this->buildVaResponsePayload($request, $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Conflicted External ID',
                            'indonesia' => 'External ID konflik',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Conflicted External ID',
                            'indonesia' => 'External ID konflik',
                        ],
                        'paymentFlagStatus' => $payment->paymentFlagStatus,
                    ]))
                );
            }
        } else { // INQUIRY
            if ($payment) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_CONFLICTED_EXTERNAL_ID',
                    '',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Conflicted External ID',
                            'indonesia' => 'External ID konflik',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Conflicted External ID',
                            'indonesia' => 'External ID konflik',
                        ],
                        'paymentFlagStatus' => $payment->paymentFlagStatus,
                    ])
                );
            }
        }
    }

    private function checkInconsistentExternalId(Request $request, $va = null)
    {
        if ($this->REQUEST_TYPE === 'PAYMENT') {
            $paymentRequestId = $request->get('paymentRequestId');
            $externalId = $request->headers->get('X-EXTERNAL-ID');
            $payment = Payment::where('paymentRequestId', $paymentRequestId)
                ->where('externalId', $externalId)
                ->where('paymentFlagStatus', $this->PAYMENT_SUCCESS_FLAG_STATUS)
                ->whereDate('created_at', DB::raw('CURDATE()'))
                ->first();
    
            if ($payment) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_INCONSISTENT_REQUEST',
                    '',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Duplicated X-EXTERNAL-ID and paymentRequestId',
                            'indonesia' => 'X-EXTERNAL-ID dan paymentRequestId terduplikasi',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Duplicated X-EXTERNAL-ID and paymentRequestId',
                            'indonesia' => 'X-EXTERNAL-ID dan paymentRequestId terduplikasi',
                        ],
                        'paymentFlagStatus' => $payment->paymentFlagStatus,
                    ])
                );
            } else if ($va) {
                Payment::create([
                    'partnerServiceId' => trim($request->get('partnerServiceId')),
                    'customerNo' => $request->get('customerNo'),
                    'virtualAccountNumber' => trim($request->get('virtualAccountNo')),
                    'virtualAccountName' => $va->user->name,
                    'trxId' => '',
                    'paymentRequestId' => $request->get('paymentRequestId'),
                    'externalId' => $request->headers->get('X-EXTERNAL-ID'),
                    'channelCode' => $request->get('channelCode'),
                    'paidAmount' => json_encode([
                        'value' => $va->outstanding,
                        'currency' => $this->CURRENCY,
                    ])
                ]);
            } else if (!$va) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_INCONSISTENT_REQUEST',
                    '',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Inconsistent request',
                            'indonesia' => 'Request inkonsisten',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Inconsistent request',
                            'indonesia' => 'Request inkonsisten',
                        ],
                        'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                    ])
                );
            }
        }
    }

    private function checkIsVaSettled($virtualAccount, $data)
    {
        $isVaSettled = $virtualAccount->outstanding === '0';

        if ($isVaSettled) {
            if ($this->REQUEST_TYPE === 'INQUIRY') {
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
            } else if ($this->REQUEST_TYPE === 'PAYMENT') {
                $virtualAccountData = [
                    'paymentFlagReason' => [
                        'english' => 'Bill has been paid',
                        'indonesia' => 'Tagihan sudah dibayar',
                    ],
                    'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                    'customerNo' => $data['customerNo'],
                    'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                    'virtualAccountName' => $virtualAccount->user->name,
                    'virtualAccountEmail' => '',
                    'virtualAccountPhone' => '',
                    'paymentRequestId' => $data['paymentRequestId'],
                    'paidAmount' => [
                        'value' => isset($data['paidAmount']) && isset($data['paidAmount']['value']) ? $data['paidAmount']['value'] : '',
                        'currency' => $this->CURRENCY,
                    ],
                    'paidBills' => '',
                    'totalAmount' => [
                        'value' => isset($data['paidAmount']) && isset($data['paidAmount']['value']) ? $data['paidAmount']['value'] : '',
                        'currency' => $this->CURRENCY,
                    ],
                    'trxDateTime' => $data['trxDateTime'],
                    'referenceNo' => $data['referenceNo'],
                    'journalNum' => '',
                    'paymentType' => '',
                    'flagAdvise' => 'N',
                    'paymentFlagStatus' => '01',
                    'billDetails' => [],
                    'freeTexts' => [],
                ];
            }
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_VALID_VA_SETTLED', '', $virtualAccountData);
        }
    }

    private function checkIsVaRegistered($virtualAccount = null, $data)
    {
        if (!$virtualAccount) {
            if ($this->REQUEST_TYPE === 'INQUIRY') {
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
            } else if ($this->REQUEST_TYPE === 'PAYMENT') {
                $virtualAccountData = [
                    'paymentFlagReason' => [
                        'english' => 'Bill not found',
                        'indonesia' => 'Tagihan tidak ditemukan',
                    ],
                    'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                    'customerNo' => $data['customerNo'],
                    'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                    'virtualAccountName' => '',
                    'virtualAccountEmail' => '',
                    'virtualAccountPhone' => '',
                    'paymentRequestId' => $data['paymentRequestId'],
                    'paidAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'paidBills' => '',
                    'totalAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'trxDateTime' => $data['trxDateTime'],
                    'referenceNo' => '',
                    'journalNum' => '',
                    'paymentType' => '',
                    'flagAdvise' => 'N',
                    'paymentFlagStatus' => '01',
                    'billDetails' => [],
                    'freeTexts' => [],
                ];
            }

            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_UNREGISTERED_VA', '', $virtualAccountData);
        }
    }

    private function checkIsVaExpired($virtualAccount, $data)
    {
        $isVaExpired = !$virtualAccount->is_active;

        if ($isVaExpired) {
            if ($this->REQUEST_TYPE === 'INQUIRY') {
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
            } else if ($this->REQUEST_TYPE === 'PAYMENT') {
                $virtualAccountData = [
                    'paymentFlagReason' => [
                        'english' => 'Invalid Bill',
                        'indonesia' => 'Tagihan kedaluarsa',
                    ],
                    'partnerServiceId' => $this->ADDITIONAL_SPACE . $data['partnerServiceId'],
                    'customerNo' => $data['customerNo'],
                    'virtualAccountNo' => $this->ADDITIONAL_SPACE . $data['virtualAccountNo'],
                    'virtualAccountName' => '',
                    'virtualAccountEmail' => '',
                    'virtualAccountPhone' => '',
                    'paymentRequestId' => $data['paymentRequestId'],
                    'paidAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'paidBills' => '',
                    'totalAmount' => [
                        'value' => '',
                        'currency' => '',
                    ],
                    'trxDateTime' => $data['trxDateTime'],
                    'referenceNo' => '',
                    'journalNum' => '',
                    'paymentType' => '',
                    'flagAdvise' => 'N',
                    'paymentFlagStatus' => '01',
                    'billDetails' => [],
                    'freeTexts' => [],
                ];
            }

            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_VALID_VA_EXPIRED', '', $virtualAccountData);
        }
    }

    private function validateRequest(
        Request $request,
        $validation,
        $virtualAccount,
        $options = [
            'additionalData' => []
        ]
    ) {
        // Check request parsing error
        $this->checkRequestParsingError($request);
        
        // Check mandatory fields
        $this->checkMandatoryFields($request, $validation);
        
        // Check invalid field format
        $this->checkInvalidHeaderFieldFormats($request);

        // Check is token valid
        $this->checkIsTokenValid($request);

        // Check valid signature 
        $this->checkIsSignatureValid($request);

        // Check is VA registered
        $this->checkIsVaRegistered($virtualAccount, $options['additionalData']);

        // Check is External ID conflicted
        $this->checkConflictedExternalId($request);

        if ($this->REQUEST_TYPE === 'PAYMENT') {
            $this->checkPaymentInvalidAmount($request);
        }

        // Check is external ID consistent
        $this->checkInconsistentExternalId($request, $virtualAccount);
        
        // Check is VA settled
        $this->checkIsVaSettled($virtualAccount, $options['additionalData']);

        // Check is VA expired
        $this->checkIsVaExpired($virtualAccount, $options['additionalData']);
    }

    private function checkIsSignatureValid(Request $request)
    {
        $httpMethod = $request->method();
        $relativeUrl = '/' . $request->path();
        $accessToken = $request->bearerToken();
        $requestBody = $request->json()->all();
        $minifiedAndCleanedRequestBody = json_encode( // minify and remove whitespace
            $requestBody,
            JSON_UNESCAPED_SLASHES
        );
        $hashedMinifiedJsonBody = hash(
            "sha256",
            $minifiedAndCleanedRequestBody
        );

        Log::info('>>> $requestBody');
        Log::notice($requestBody);
        Log::info('>>> minifiedAndCleanedRequestBody');
        Log::notice($minifiedAndCleanedRequestBody);
        Log::info('>>> $hashedMinifiedJsonBody');
        Log::notice($hashedMinifiedJsonBody);

        $signature = $request->header('X-SIGNATURE');
        $timestampStr = $request->header('X-TIMESTAMP');
        $stringToSign = "$httpMethod:$relativeUrl:$accessToken:$hashedMinifiedJsonBody:$timestampStr";

        Log::info('>>> $stringToSign');
        Log::notice($stringToSign);

        $bcaSecret = $this->CLIENT->secret;

        Log::info('>>> $bcaSecret');
        Log::debug($bcaSecret);

        $signatureTester = base64_encode(
            hash_hmac(
                "sha512",
                $stringToSign,
                $bcaSecret,
                true
            )
        );

        Log::info('>>> $signature');
        Log::debug($signature);
        Log::info('>>> $signatureTester');
        Log::debug($signatureTester);

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
	    if (!$client) {
		    $client = OAuthClient::find($clientId);
	    }
            $this->CLIENT = $client;
        } else {
		$client = OAuthClient::find($clientId);
		$this->CLIENT = $client;
        }

        if (!$client) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_UNAUTHORIZED_UNKNOWN_CLIENT');
        }

        $authorization = $request->bearerToken();
        
        if (!$authorization) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_ACCESS_TOKEN_INVALID');
        }

        $validated = Token::validate($authorization, $client->secret);

        if (!$validated) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_ACCESS_TOKEN_INVALID');
        }
    }

    private function validateResponse($response)
    {
        Log::info('Validating response');
        Log::notice($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new SnapRequestParsingException($this->REQUEST_TYPE . '_RESPONSE_PARSING_ERROR');
        }
    }

    private function checkInvalidHeaderFieldFormats(Request $request)
    {
        if ($request->has("grantType")) {
            $hasValidGrantValue = $request->get('grantType') === 'client_credentials';

            if (!$hasValidGrantValue) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD',
                    ' {"grantType": "missing field"}',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                    ])
                );
            }

            $isClientKeyExist = $request->headers->has('X-CLIENT-KEY');

            if (!$isClientKeyExist) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD',
                    ' {"X-CLIENT-KEY": "missing field"}',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                    ])
                );
            }
        } else {
            $hasValidHeadersValue = $request->headers->has('X-EXTERNAL-ID');

            if (!$hasValidHeadersValue) {
                throw new SnapRequestParsingException(
                    $this->REQUEST_TYPE . '_INVALID_MANDATORY_FIELD',
                    ' {"X-EXTERNAL-ID": "missing field"}',
                    $this->buildVaResponsePayload($request, [
                        'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                        'inquiryReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagReason' => [
                            'english' => 'Missing mandatory field',
                            'indonesia' => 'Isian tidak lengkap',
                        ],
                        'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                    ])
                );
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
        $invalidMandatoryHeaders = [];
        if (!$request->headers->has('CHANNEL-ID')) {
            $invalidMandatoryHeaders[] = 'CHANNEL-ID';
        }

        if (!$request->headers->has('X-PARTNER-ID')) {
            $invalidMandatoryHeaders[] = 'X-PARTNER-ID';
        }

        if (!$request->headers->has('X-EXTERNAL-ID')) {
            $invalidMandatoryHeaders[] = 'X-EXTERNAL-ID';
        }

        $isHeadersValid = sizeof($invalidMandatoryHeaders) === 0;

        if (!$isHeadersValid) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_MISSING_MANDATORY_FIELD',
                ' [' . implode(', ', $invalidMandatoryHeaders) . ']',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Isian tidak lengkap',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Isian tidak lengkap',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        $virtualAccountNo = $request->input('virtualAccountNo');
        $partnerServiceId = $request->input('partnerServiceId');
        $customerNo = $request->input('customerNo');
        
        if (!$virtualAccountNo || !$partnerServiceId || !$customerNo) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_MISSING_MANDATORY_FIELD',
                !$virtualAccountNo ? ' virtualAccountNo' : (
                    !$partnerServiceId ? ' partnerServiceId' : (
                        !$customerNo ? ' customerNo' : ''
                    )
                ),
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Request tidak lengkap',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Request tidak lengkap',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }
        
        // Validate length, max 20 char
        if (strlen($customerNo) > 20) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_FIELD_FORMAT',
                ' {customerNo: "exceed 20 characters"}',
            );
        }
        
        // Validate customerNo contains string
        if ($customerNo && !is_numeric(trim($customerNo))) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_FIELD_FORMAT',
                ' {customerNo: "not a number"}',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'customerNo is not a number',
                        'indonesia' => 'customerNo tidak valid',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'customerNo is not a number',
                        'indonesia' => 'customerNo tidak valid',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        // Validate VA contains string
        if ($virtualAccountNo && !is_numeric(trim($virtualAccountNo))) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_FIELD_FORMAT',
                ' {virtualAccountNo: "not a number"}',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'virtualAccountNo is not a number',
                        'indonesia' => 'virtualAccountNo tidak valid',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'virtualAccountNo is not a number',
                        'indonesia' => 'virtualAccountNo tidak valid',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        // Validate VA number
        if ($partnerServiceId . $customerNo !== $virtualAccountNo) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_FIELD_FORMAT',
                ' {customerNo: "VA not matched"}',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'VA not matched',
                        'indonesia' => 'VA tidak sesuai',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'VA not matched',
                        'indonesia' => 'VA tidak sesuai',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        // Validate whitespace
        if (strpos($virtualAccountNo, $this->ADDITIONAL_SPACE) === FALSE) {
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_INVALID_FIELD_FORMAT',
                ' {virtualAccountNo: "invalid additional space"}',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'virtualAccountNo invalid additional space',
                        'indonesia' => 'virtualAccountNo invalid spasi',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'virtualAccountNo invalid additional space',
                        'indonesia' => 'virtualAccountNo invalid spasi',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }

        // Check mandatory body
        $bodyValidator = Validator::make($request->all(), $validation);

        if ($bodyValidator->fails()) {
            $messages = $bodyValidator->getMessageBag();
            $failedAttributes = array_keys($messages->getMessages());
            $additionalMessage = implode(', ', $failedAttributes);
            throw new SnapRequestParsingException(
                $this->REQUEST_TYPE . '_MISSING_MANDATORY_FIELD',
                ' [' . $additionalMessage . ']',
                $this->buildVaResponsePayload($request, [
                    'inquiryStatus' => $this->INQUIRY_INVALID_STATUS,
                    'inquiryReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Isian tidak lengkap',
                    ],
                    'paymentFlagReason' => [
                        'english' => 'Missing mandatory field',
                        'indonesia' => 'Isian tidak lengkap',
                    ],
                    'paymentFlagStatus' => $this->PAYMENT_INVALID_STATUS,
                ])
            );
        }
    }

    private function constructBillDetails(VirtualAccount $va) {
        $description = $va && $va->description ? json_decode($va->description) : null;

        if (!$description || !is_array($description)) {
            return [
                [
                    'billNo' => $va->number,
                    'billDescription' => [
                        'english' => 'School Payment',
                        'indonesia' => 'Pembayaran Sekolah',
                    ],
                    'billSubCompany' => $this->INQUIRY_SUB_COMPANY,
                    'billAmount' => [
                        'value' => $va ? $va->outstanding : '',
                        'currency' => $this->CURRENCY,
                    ],
                ],
            ];
        }

        $details = [];

        foreach ($description as $desc) {
            $details[] = [
                'billNo' => $va->number,
                'billDescription' => [
                    'english' => $desc->name ? $desc->name : '',
                    'indonesia' => $desc->name ? $desc->name : '',
                ],
                'billSubCompany' => $this->INQUIRY_SUB_COMPANY,
                'billAmount' => [
                    'value' => $desc && $desc->value ? $desc->value : '',
                    'currency' => $this->CURRENCY,
                ],
            ];
        }

        return $details;
    }
}
