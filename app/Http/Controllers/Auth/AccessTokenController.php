<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\SnapRequestParsingException;
use App\Helpers\BcaHelper;
use App\Models\OAuthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use ReallySimpleJWT\Token;

class AccessTokenController extends Controller
{
    protected $expirationInSeconds = 900;

    public function issueToken(Request $request)
    {
        try {
            Log::info('>> INITIATE ISSUE TOKEN, REQUEST:');
            Log::info($request);

            $client = $this->validateRequestAndGetClient($request);
            $expiration = time() + $this->expirationInSeconds;
            $issuer = config('app.url');
            $token = Token::create(
                $client->user_id,
                $client->secret,
                $expiration,
                $issuer,
                [ 'fixed_secret_length_enabled' => false ]
            );

            $response = [
                'responseCode' => '2007300',
                'responseMessage' => 'Successful',
                'accessToken' => $token,
                'tokenType' => 'bearer',
                'expiresIn' => $this->expirationInSeconds,
            ];

            $jsonResponse = response()->json($response);

            Log::info('>> SUCCESS RESPONSE:');
            Log::info($jsonResponse);
    
            return $jsonResponse;
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

    public function validateToken(Request $request)
    {
        $clientId = $request->headers->get('X-CLIENT-KEY');

        if (!$clientId) {
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
        }

        $client = OAuthClient::find($clientId);
        $authorization = $request->bearerToken();
        $validated = Token::validate($authorization, $client->secret);

        return response()->json([
            'validated' => $validated
        ]);
    }

    private function validateRequestAndGetClient(Request $request)
    {
        // Validate grant type

        $validGrantType = 'client_credentials';
        $isGrantTypeValid = $request->get('grantType') === $validGrantType;
        
        if (!$isGrantTypeValid) {
            throw new SnapRequestParsingException('INVALID_FIELD_FORMAT');
        }

        // Validate timestamp

        $timestamp = null;

        try {
            $timestampStr = $request->header('X-TIMESTAMP');
            $timestamp = Carbon::parse($timestampStr);
            $expiredTimestampThreshold = Carbon::now()->subSeconds($this->expirationInSeconds);
            $isExpired = $timestamp < $expiredTimestampThreshold;
            $isFuture = $timestamp > Carbon::now();

            if ($isExpired || $isFuture) {
                throw new SnapRequestParsingException('INVALID_TIMESTAMP_FORMAT');
            }

        } catch (\Carbon\Exceptions\InvalidFormatException $e) {
            throw new SnapRequestParsingException('INVALID_TIMESTAMP_FORMAT');
        }

        // Validate client key

        $clientId = $request->header('X-CLIENT-KEY');

        if (!$clientId) {
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
        }
        
        // Validate client
        
        $client = OAuthClient::find($clientId);

        if (!$client) {
            throw new SnapRequestParsingException('UNAUTHORIZED_UNKNOWN_CLIENT');
        }

        // Validate signature

        $requestedAsymmetricSignature = $request->header('X-SIGNATURE');
        $calculatedAsymmetricSignature = BcaHelper::getSignature($clientId, $timestamp);

        if ($requestedAsymmetricSignature !== $calculatedAsymmetricSignature) {
            throw new SnapRequestParsingException('UNAUTHORIZED_SIGNATURE');
        }

        return $client;
    }
}
