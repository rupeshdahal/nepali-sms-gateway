<?php

namespace RupeshDai\NepaliSmsGateway\Exceptions;

use Exception;

class SmsException extends Exception
{
    /**
     * Create a new SMS exception instance.
     *
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     * @return void
     */
    public function __construct(string $message = '', int $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create a new configuration error exception.
     *
     * @param string $gateway
     * @return static
     */
    public static function configurationError(string $gateway): self
    {
        return new static("SMS gateway [{$gateway}] is not properly configured.");
    }

    /**
     * Create a new unsupported gateway exception.
     *
     * @param string $gateway
     * @return static
     */
    public static function unsupportedGateway(string $gateway): self
    {
        return new static("SMS gateway [{$gateway}] is not supported.");
    }

    /**
     * Create a new gateway error exception.
     *
     * @param string $gateway
     * @param string $message
     * @return static
     */
    public static function gatewayError(string $gateway, string $message): self
    {
        return new static("SMS gateway [{$gateway}] error: {$message}");
    }

    /**
     * Create a new invalid phone number exception.
     *
     * @param string $phoneNumber
     * @return static
     */
    public static function invalidPhoneNumber(string $phoneNumber): self
    {
        return new static("Invalid phone number format: {$phoneNumber}");
    }
}
