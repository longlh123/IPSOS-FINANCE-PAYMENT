<?php

namespace App\Exceptions;

use Exception;

class GotItVoucherException extends Exception
{
    protected $userMessage;
    protected $logContext;

    public function __construct(
        string $userMessage = 'Voucher không hợp lệ.', 
        string $logContext = '', 
        int $code=400, 
        \Throwable $previous=null)
    {
        parent::__construct($userMessage, $code, $previous);
        
        $this->userMessage = $userMessage;
        $this->logContext = $logContext . ': ' . $userMessage;
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getLogContext(): string
    {
        return $this->logContext;
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'GotItVoucherException',
            'message' => $this->getUserMessage(),
            'context' => $this->getLogContext(),
        ], $this->getCode() ?: 400);
    }
}
