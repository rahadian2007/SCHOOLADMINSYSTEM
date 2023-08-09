<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Support\Facades\Log;
use stdClass;

class SnapRequestParsingException extends Exception
{
    private $additionalMessage;
    private $virtualAccountData;

    public function __construct($message, $_additionalMessage = '', $_virtualAccountData = null)
    {
        parent::__construct($message);
        $this->additionalMessage = $_additionalMessage;
        $this->virtualAccountData = $_virtualAccountData;
    }

    public function report(): void
    {
    }

    public function render()
    {
        try {
            Log::info("app.".$this->getMessage());
            Log::warning(request());
            $error = config("app.".$this->getMessage());
            $jsonResponseData = [
                'responseCode' => $error['CODE'],
                'responseMessage' => $error['MSG'] . $this->additionalMessage,
            ];

            if ($this->virtualAccountData) {
                $jsonResponseData['virtualAccountData'] = $this->virtualAccountData;
                $jsonResponseData['additionalInfo'] = new stdClass;
            }
            
            $response = response()->json($jsonResponseData, $error['HTTP_CODE']);
    
            return $response;
        } catch (Exception $error) {
            Log::error($error);
        }
    }
}
