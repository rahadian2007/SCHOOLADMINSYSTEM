<?php

namespace App\Exceptions;

use Exception;

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
        $error = config("app.".$this->getMessage());
        return response()->json([
            'responseCode' => $error['CODE'],
            'responseMessage' => $error['MSG'] . ' ' . $this->additionalMessage,
            'virtualAccountData' => [],
        ]);
    }
}
