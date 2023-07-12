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

        openssl_sign($stringToSign, $binarySignature, $privateKey, "SHA256");
        $signature = base64_encode($binarySignature);

        return [
            "X-CLIENT-KEY" => $clientId,
            "X-TIMESTAMP" => $timestamp,
            "X-SIGNATURE" => $signature,
        ];
    }

    /**
     * Symmetric header to make sure payload integrity
     * @param String $httpMethod (must be upper cased)
     * @param String $relativeUriPath
     * @param String $accessToken
     * @param Object $body
     * @return Array $headers
     */
    public static function getSymmetricHeaders($httpMethod, $relativeUriPath, $accessToken, $body = null)
    {
        $timestamp = Carbon::now()
            ->timezone("Asia/Jakarta")
            ->toIso8601String();

        $hashedMinifiedJsonBody = hash(
            "sha256",
            json_encode( // minify and remove whitespace
                $body,
                JSON_UNESCAPED_SLASHES
            )
        );

        $stringToSign = "{$httpMethod}:{$relativeUriPath}:{$accessToken}:{$hashedMinifiedJsonBody}:{$timestamp}";

        $signature = base64_encode(
            hash_hmac(
                "sha512",
                $stringToSign,
                config('app.bca_client_secret'),
                true
            )
        );
        
        Log::info("String to sign");
        Log::info($stringToSign);

        return [
            "Authorization" => "Bearer $accessToken",
            "X-TIMESTAMP" => $timestamp,
            "X-SIGNATURE" => $signature,
            "X-EXTERNAL-ID" => (int) date("YmdHms"),
            "X-CLIENT-KEY" => config('app.bca_client_id'),
            "Content-Type" => "application/json",
            "X-PARTNER-ID" => config('app.bca_company_id'),
            "CHANNEL-ID" => 95231, // Virtual account channel id = 95231
        ];
    }

    /**
     * Check whether access token is in cache. Create new one when unavailable.
     */
    public static function evalAccessToken()
    {
        try {
            if (!Cache::has(static::$accessTokenSessionPath)) {
                $requestUrl = config('app.bca_api_base_url') . "/openapi/v1.0/access-token/b2b";
                $requestBody = [ "grantType" => "client_credentials" ];
                $response = Http::acceptJson()
                    ->withHeaders(BcaHelper::getAsymmetricHeaders())
                    ->post($requestUrl, $requestBody);
                $jsonResponse = $response->json();
                $accessToken = $jsonResponse["accessToken"];
                if ($accessToken) {
                    Cache::put(static::$accessTokenSessionPath, $accessToken, 900);
                }
            }
        } catch (Exception $error) {
            Log::error($error);
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
            Log::error($error);
        }
    }
    
    public static function getTransferVaStatus()
    {
        try {
            $relativeUriPath = "/openapi/v1.0/transfer-va/status";
            $spacer = "   ";
            $accessToken = Cache::get(static::$accessTokenSessionPath);
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;
            $partnerServiceId = $spacer . config('app.bca_company_id');
            $customerNumber = "01";
            $requestBody = [
                "partnerServiceId" => $partnerServiceId,
                "customerNo" => $customerNumber,
                "virtualAccountNo" => $partnerServiceId . $customerNumber,
                "paymentRequestId" => "202202111031031234500001136962",
            ];

            Log::info("Request to endpoint: $requestUrl");

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
            Log::error($error);
        }
    }
}