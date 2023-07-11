<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BcaHelper {

    public static $accessTokenSessionPath = "bca_access_token";

    /**
     * Asymmetric header to get access token
     */
    public static function getAsymmetricHeaders()
    {
        $clientId = config('app.bca_client_id');
        $timestamp = Carbon::now()->timezone("Asia/Jakarta")->toIso8601String();
        $stringToSign = "$clientId|$timestamp";
        $privateKey = openssl_get_privatekey(config('app.bca_private_key'));
        $algo = "SHA256";

        openssl_sign($stringToSign, $binarySignature, $privateKey, $algo);
        $signature = base64_encode($binarySignature);

        return [
            "X-CLIENT-KEY" => $clientId,
            "X-TIMESTAMP" => $timestamp,
            "X-SIGNATURE" => $signature,
        ];
    }

    /**
     * Symmetric header to make sure payload integrity
     */
    public static function getSymmetricHeaders($httpMethod, $relativeUriPath, $accessToken, $body = "", $useAdditionalHeaders = false)
    {
        $timestamp = Carbon::now()->timezone("Asia/Jakarta")->toIso8601String();
        $minifiedJsonBody = json_encode($body);
        $bodyHashAlgo = "SHA256";
        $hashedMinifiedJsonBody = hash($bodyHashAlgo, $minifiedJsonBody);
        $stringToSign = "$httpMethod:$relativeUriPath:$accessToken:$hashedMinifiedJsonBody:$timestamp";
        $clientSecret = config('app.bca_client_secret');
        $signatureHashAlgo = "sha512";
        $signature = base64_encode(hash_hmac($signatureHashAlgo, $stringToSign, $clientSecret));
        // $signature = hash_hmac($signatureHashAlgo, $stringToSign, $clientSecret);
        $externalId = (int) date("YmdHms");

        $headers = [
            "Authorization" => "Bearer $accessToken",
            "X-TIMESTAMP" => $timestamp,
            "X-SIGNATURE" => $signature,
            "X-EXTERNAL-ID" => $externalId,
        ];

        if ($useAdditionalHeaders) {
            $channelId = "95231";
            $additionalHeaders = [
                "X-PARTNER-ID" => config('app.bca_company_id'),
                "CHANNEL-ID" => $channelId,
            ];

            $headers = array_merge($headers, $additionalHeaders);
        }

        return $headers;
    }

    /**
     * Check whether access token is in cache. Create new one when unavailable.
     */
    public static function evalAccessToken()
    {
        try {
            if (!Cache::has(static::$accessTokenSessionPath)) {
                $asymmetricHeaders = BcaHelper::getAsymmetricHeaders();
                $requestUrl = config('app.bca_api_base_url') . "/openapi/v1.0/access-token/b2b";
                $requestBody = [ "grantType" => "client_credentials" ];
                $response = Http::acceptJson()
                    ->withHeaders($asymmetricHeaders)
                    ->post($requestUrl, $requestBody);
                $jsonResponse = $response->json();
                $accessToken = $jsonResponse["accessToken"];
                if ($accessToken) {
                    Cache::put(static::$accessTokenSessionPath, $accessToken, 900);
                }
            }
        } catch (Exception $error) {
            // TODO: Handle token error
        }
    }

    public static function getAccountInfo()
    {
        try {
            $httpMethod = "POST";
            $relativeUriPath = "/fire/accounts";
            $accessToken = Cache::get(static::$accessTokenSessionPath);
            $symmetricHeaders = BcaHelper::getSymmetricHeaders(
                $httpMethod,
                $relativeUriPath,
                $accessToken,
                "",
                true
            );
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;
            $response = Http::acceptJson()
                ->withHeaders($symmetricHeaders)
                ->post($requestUrl);
            return $response->json();
        } catch (Exception $error) {
            dd($error);
            // TODO: Handle token error
        }
    }
    
    public static function getTransferVaStatus()
    {
        try {
            $httpMethod = "POST";
            $relativeUriPath = "/openapi/v1.0/transfer-va/status";
            $accessToken = Cache::get(static::$accessTokenSessionPath);
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;
            Log::info("Request to: $requestUrl");
            $symmetricHeaders = BcaHelper::getSymmetricHeaders(
                $httpMethod,
                $relativeUriPath,
                $accessToken,
                "",
                true
            );
            Log::info("Headers:");
            Log::info($symmetricHeaders);
            $spacer = "        ";
            $partnerServiceId = $spacer . config('app.bca_company_id');
            $customerNumber = "01";
            $requestBody = [
                "partnerServiceId" => $partnerServiceId,
                "customerNo" => $customerNumber,
                "virtualAccountNo" => $partnerServiceId . $customerNumber,
                "paymentRequestId" => "202202111031031234500001136962",
            ];
            Log::info("Request Body:");
            Log::info($requestBody);
            $response = Http::acceptJson()
                ->withHeaders($symmetricHeaders)
                ->post($requestUrl, $requestBody);
            Log::info("Response:");
            Log::info($response);
            return $response->json();
        } catch (Exception $error) {
            Log::error($error);
        }
    }

    public static function createVirtualAccountPaymentFlag()
    {
        try {
            $httpMethod = "POST";
            $relativeUriPath = "/openapi/v1.0/transfer-va/payment";
            $accessToken = Cache::get(static::$accessTokenSessionPath);
            $headers = BcaHelper::getSymmetricHeaders(
                $httpMethod,
                $relativeUriPath,
                $accessToken,
                "",
                null,
                true
            );
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;
            $response = Http::acceptJson()
                ->withHeaders($headers)
                ->post($requestUrl);
            return $response->json();
        } catch (Exception $error) {
            dd($error);
            // TODO: Handle token error
        }
    }
}