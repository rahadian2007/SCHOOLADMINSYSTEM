<?php

namespace App\Http\Controllers;

use App\Exceptions\SnapRequestParsingException;
use App\Models\VirtualAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SnapVaInboundController extends Controller
{
    private $CURRENCY = 'IDR';
    private $INQUIRY_RESP_STATUS_SUCCESS = '2002400';
    private $INQUIRY_PROC_STATUS_SUCCESS = '00';
    private $INQUIRY_PROC_REASON_SUCCESS_ID = 'Sukses';
    private $INQUIRY_PROC_REASON_SUCCESS_EN = 'Success';
    private $INQUIRY_MSG_SUCCESS = 'Successful';
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
        $this->validateRequest($request, []);

        return response()->json($request->all());
    }

    /**
     * /transfer-va/inquiry
     */
    public function transferVaInquiry(Request $request)
    {
        $this->validateRequest($request, [
            'partnerServiceId' => 'required|string',
            'customerNo' => 'required|string',
            'virtualAccountNo' => 'required|string',
            'trxDateInit' => 'required|string',
            'channelCode' => 'required|numeric',
            'inquiryRequestId' => 'required|numeric',
        ]);

        extract($request->all());

        $va = VirtualAccount::where('number', $virtualAccountNo)->first();

        if (!$va) {
            throw new SnapRequestParsingException('VALID_VA_UNREGISTERED');
        }

        $data = [
            'responseCode' => $this->INQUIRY_RESP_STATUS_SUCCESS,
            'responseMessage' => $this->INQUIRY_MSG_SUCCESS,
            'virtualAccountData' => [
                'inquiryStatus' => $this->INQUIRY_PROC_STATUS_SUCCESS,
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

        return $response;
    }

    /**
     * /transfer-va/payment
     */
    public function transferVaPayment(Request $request)
    {
        $this->validateRequest($request, [
            'partnerServiceId' => 'required|string',
            'customerNo' => 'required|string',
            'virtualAccountNo' => 'required|string',
            'virtualAccountName' => 'required|string',
            'paymentRequestId' => 'required|string',
            'channelCode' => 'required|numeric',
            'paidAmount.value' => 'required|string',
            'paidAmount.currency' => 'required|string',
            'flagAdvise' => 'required|string',
        ]);

        extract($request->all());

        $va = VirtualAccount::where('number', $virtualAccountNo)->first();

        if (!$va) {
            throw new SnapRequestParsingException('VALID_VA_UNREGISTERED');
        }

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

        return $response;
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

    private function validateRequest(Request $request, $validation)
    {
        // Check request parsing error
        $this->checkRequestParsingError($request);

        // Check mandatory fields
        $this->checkMandatoryFields($request, $validation);

        // Check invalid field format
        // $this->checkInvalidHeaderFieldFormats($request);

        // TODO: Check is External ID conflicted
        // TODO: Check is VA settled
        // TODO: Check is VA expired
        // TODO: Check is VA unregistered
        // TODO: Check response parsing error
    }

    private function validateResponse($response)
    {
        // TODO: validate response
        return true;
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
            $hasValidHeadersValue = $request->headers->has('X-CLIENT-ID');

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
