<?php

namespace App\Exceptions;

use Exception;

use Illuminate\Support\Facades\Log;

class SnapRequestParsingException extends Exception
{
    private $additionalMessage;

    public function __construct($message, $_additionalMessage = '')
    {
        parent::__construct($message);
        $this->additionalMessage = $_additionalMessage;
    }

    public function report(): void
    {
        // TODO: add to log
    }

    public function render()
    {
        try {
            $error = config("app.".$this->getMessage());
            $response = response()->json([
                'responseCode' => $error['CODE'],
                'responseMessage' => $error['MSG'] . ' ' . $this->additionalMessage,
                'virtualAccountData' => [],
            ]);
    
            Log::warning("INITIATE TRANSFER VA INQUIRY");
    
            return $response;
        } catch (Exception $error) {
            Log::error($error);
        }
    }
}
