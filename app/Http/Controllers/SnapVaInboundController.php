<?php

namespace App\Http\Controllers;

use App\Exceptions\SnapRequestParsingException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SnapVaInboundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateVaPaymentFlag(Request $request)
    {
        // Check request parsing error
        $this->checkRequestParsingError($request);

        // Check mandatory fields
        $this->checkMandatoryFields($request);

        // TODO: Check invalid field format
        // TODO: Check is External ID conflicted
        // TODO: Check is VA settled
        // TODO: Check is VA expired
        // TODO: Check is VA unregistered
        // TODO: Check response parsing error

        return response()->json($request->all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function checkRequestParsingError(Request $request)
    {
        json_decode($request->getContent());

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new SnapRequestParsingException('REQUEST_PARSING_ERROR');
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
    private function checkMandatoryFields(Request $request)
    {
        // Check mandatory headers
        $isHeadersValid = $request->headers->has('CHANNEL-ID') && $request->headers->has('X-PARTNER-ID');

        if (!$isHeadersValid) {
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
        }

        // Check mandatory body
        $bodyValidator = Validator::make($request->all(), [
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

        if ($bodyValidator->fails()) {
            $messages = $bodyValidator->getMessageBag();
            $failedAttributes = array_keys($messages->getMessages());
            $additionalMessage = implode(', ', $failedAttributes);
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD', $additionalMessage);
        }
    }
}
