<?php

namespace App\Http\Controllers;

use App\Exceptions\SnapRequestParsingException;
use App\Helpers\BcaHelper;
use App\Models\VirtualAccount;
use App\Models\Payment;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnapVaOutboundController extends Controller
{
    public function index()
    {
        dd('a');
    }

    public function updateVaStatus(VirtualAccount $va)
    {
        try {
            Log::info(">> INITIATE OUTBOUND REQUEST");
            BcaHelper::evalAccessToken();
            
            $relativeUriPath = "/openapi/v1.0/transfer-va/status";
            $spacer = "   ";
            $accessToken = Cache::get(BcaHelper::$accessTokenSessionPath);
            
            Log::info(">> Access Token");
            Log::info($accessToken);
            
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;

            Log::info(">> Request URL");
            Log::info($requestUrl);

            $lastPayment = Payment::where('virtualAccountNumber', $va->number)->OrderBy('id', 'desc')->first();
            $customerNumber = str_replace($lastPayment->partnerServiceId, '', $va->number);
            
            Log::info(">> Last Payment");
            Log::info($lastPayment);

            if (!$lastPayment->id) {
                throw new SnapRequestParsingException('STATUS_PAYMENT_NOT_FOUND');
            }
            
            $lastPaymentRequestId = $lastPayment->paymentRequestId;
            $partnerServiceId = $spacer . $lastPayment->partnerServiceId;
            $requestBody = [
                "partnerServiceId" => $partnerServiceId,
                "customerNo" => $customerNumber,
                "virtualAccountNo" => $partnerServiceId . $customerNumber,
                "paymentRequestId" => $lastPaymentRequestId,
            ];

            $symmetricHeaders = BcaHelper::getSymmetricHeaders(
                "POST",
                $relativeUriPath,
                $accessToken,
                $requestBody
            );

            Log::info("Headers:");
            Log::info($symmetricHeaders);
            Log::info("Request Body:");
            Log::info($requestBody);

            $response = Http::acceptJson()
                ->withHeaders($symmetricHeaders)
                ->post($requestUrl, $requestBody);

            Log::info("Response:");
            Log::info($response);

            return $response->json();
        } catch (SnapRequestParsingException $error) {
            return redirect()->back()->withErrors("Terjadi kesalahan. Silakan hubungi Admin");
        } catch (Exception $error) {
            Log::error($error);
        }
    }
}
