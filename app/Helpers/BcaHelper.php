<?php

namespace App\Helpers;

use App\Models\VirtualAccount;
use App\Exceptions\SnapRequestParsingException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class BcaHelper {

    public static $accessTokenSessionPath = "bca_access_token";

    public static function getSignature($clientId, $timestamp)
    {
        $stringToSign = "$clientId|$timestamp";
        $privateKey = openssl_get_privatekey(config('app.bca_private_key'));
        openssl_sign($stringToSign, $binarySignature, $privateKey, "SHA256");
        $signature = base64_encode($binarySignature);

        return $signature;
    }

    /**
     * Asymmetric header to get access token
     */
    public static function getAsymmetricHeaders()
    {
        // $clientId = config('app.bca_client_id');
        $clientId = config('app.bca_client_id');
        $timestamp = Carbon::now()->timezone("Asia/Jakarta")->toIso8601String();
        $signature = self::getSignature($clientId, $timestamp);

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
                'XtA6VKJtlcuaZ2F9l3d7ksKcmUCRL7I17pSzLRA5',
                true
            )
        );
        
        Log::info("String to sign");
        Log::info($stringToSign);

        return [
            // "String to sign" => $stringToSign,
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
            $hasAccessTokenInCache = Cache::has(static::$accessTokenSessionPath);
            Log::info(">> hasAccessTokenInCache");
            Log::info($hasAccessTokenInCache);
            if (!$hasAccessTokenInCache) {
                $requestUrl = config('app.bca_api_base_url') . "/openapi/v1.0/access-token/b2b";
                $requestBody = [ "grantType" => "client_credentials" ];

                Log::info(">> get token url");
                Log::info($requestUrl);
                Log::info(">> requestBody");
                Log::info($requestBody);

                $response = Http::acceptJson()
                    ->withHeaders(BcaHelper::getAsymmetricHeaders())
                    ->post($requestUrl, $requestBody);
                
                $jsonResponse = $response->json();
                
                Log::info(">> token response");
                Log::info($jsonResponse);

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
    
    public static function getTransferVaStatus(VirtualAccount $va)
    {
        try {
            self::evalAccessToken();
            
            $relativeUriPath = "/openapi/v1.0/transfer-va/status";
            $spacer = "   ";
            $accessToken = Cache::get(static::$accessTokenSessionPath);
            $requestUrl = config('app.bca_api_base_url') . $relativeUriPath;
            $partnerServiceId = $spacer . config('app.bca_company_id');
            $customerNumber = "01";

            if (!$va->payment) {
                throw new SnapRequestParsingException('STATUS_PAYMENT_NOT_FOUND');
            }

            $lastPaymentRequestId = $va->payment->last()->paymentRequestId;
            $requestBody = [
                "partnerServiceId" => $partnerServiceId,
                "customerNo" => $customerNumber,
                "virtualAccountNo" => $partnerServiceId . $customerNumber,
                "paymentRequestId" => $lastPaymentRequestId,
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
        } catch (SnapRequestParsingException $error) {
            return redirect()->back()->withErrors(json_encode($error));
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

    public static function verifySignature()
    {
        // $signature = 'WFkVWY0DpihFbOUuxnH19txd3t2H4msRjqRI58MVwjH+I4tW2+DIJLpWijr6O4FrbT28x1+fS5v85GWhRGwnbeL4S2l3cBCJfQciBu8pJNC2rqkldYBCFa2Xv1Eh3Fmva5KbrfN2E8E0X2oDtUcNYoh0QcijZseUXRkIXN6el9drToeN9y5uZ93i3RWTCtO6sqvu0deP8jhI74aEWY2ug7SO3A8FlZ6n/XIM71fqpYVEIDvFaOSTyHVpSub7mnF1sbF70ub6jVukL35NvpYnAmVqueA0CG2+QfsZiRiiXlshvloC1olDH1TJmSEllrzEtd+J6OOrcEbaTB8uOBNSMg==';
        $stringToSign = "99a07b36-c73b-48a5-99db-a53acca60833|2023-08-04T11:28:28+07:00";
        $privateKey = openssl_get_privatekey(config('app.bca_private_key'));
        Log::info("string to sign: " . $stringToSign);
        Log::info("private key location: " . config('app.bca_private_key'));
        openssl_sign($stringToSign, $binarySignature, $privateKey, "SHA256");
        $signature = base64_encode($binarySignature);
        Log::info("signature: " . $signature);
        
        $publicKey = openssl_get_publickey(config('app.bca_public_key'));
        Log::info("public key location: " . config('app.bca_public_key'));
        $isSignatureVerified = openssl_verify(
            $stringToSign,
            $signature,
            $publicKey,
            'SHA256'
        );
        Log::info("result: " . $isSignatureVerified);
        return "true";
    }
}