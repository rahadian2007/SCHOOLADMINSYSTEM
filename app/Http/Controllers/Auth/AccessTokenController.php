<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Exceptions\SnapRequestParsingException;
use App\Models\OAuthClient;
use Illuminate\Http\Request;
use ReallySimpleJWT\Token;

class AccessTokenController extends Controller
{
    public function issueToken(Request $request)
    {
        $clientId = $request->headers->get('X-CLIENT-KEY');
        if (!$clientId) {
            throw new SnapRequestParsingException('INVALID_MANDATORY_FIELD');
        }

        $client = OAuthClient::find($clientId);
        $expirationInSeconds = 900;
        $expiration = time() + $expirationInSeconds;
        $issuer = config('app.url');
        $token = Token::create($client->user_id, $client->secret, $expiration, $issuer, [ 'fixed_secret_length_enabled' => false ]);

        $response = [
            'responseCode' => '2007300',
            'responseMessage' => 'Successful',
            'accessToken' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => $expirationInSeconds,
        ];

        return response()->json($response);
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
}
