<?php

namespace App\Exceptions;

use Exception;

class BusinessLogicException extends Exception
{
    public string $key;

    /**
     * @param string $message 
     * @param string $key
     * @param int $code
     */
    public function __construct(string $message, string $key = 'error', int $code = 422)
    {
        parent::__construct($message, $code);
        $this->key = $key;
    }
}