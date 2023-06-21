<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class BcaHelper {

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
            "clientId" => $clientId,
            "timestamp" => $timestamp,
            "signature" => $signature,
        ];
    }

    /**
     * Symmetric header to make sure payload integrity
     */
    public static function getSymmetricHeaders($httpMethod, $relativeUriPath, $accessToken, $body = "")
    {
        $timestamp = Carbon::now()->timezone("Asia/Jakarta")->toIso8601String();
        $minifiedJsonBody = json_encode($body);
        $bodyHashAlgo = "SHA256";
        $hashedMinifiedJsonBody = hash($bodyHashAlgo, $minifiedJsonBody);
        $stringToSign = "$httpMethod:$relativeUriPath:$accessToken:$hashedMinifiedJsonBody:$timestamp";
        $clientSecret = config('app.bca_client_secret');
        $signatureHashAlgo = "sha512";
        $signature = hash_hmac($signatureHashAlgo, $stringToSign, $clientSecret);

        return [
            "uri" => $relativeUriPath,
            "authorization" => "Bearer $accessToken",
            "externalId" => "<Unique reference number>",
            "timestamp" => $timestamp,
            "signature" => $signature,
        ];
    }
}