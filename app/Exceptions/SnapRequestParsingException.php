<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Support\Facades\Log;

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
        // TODO: add to log
    }

    public function render()
    {
        try {
            $error = config("app.".$this->getMessage());
            $jsonResponseData = [
                'responseCode' => $error['CODE'],
                'responseMessage' => $error['MSG'] . $this->additionalMessage,
            ];

            if ($this->virtualAccountData) {
                $jsonResponseData['virtualAccountData'] = $this->virtualAccountData;
            }
            
            $response = response()->json($jsonResponseData, $error['HTTP_CODE']);
    
            return $response;
        } catch (Exception $error) {
            Log::error($error);
        }
    }
}
