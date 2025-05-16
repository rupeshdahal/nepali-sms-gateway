<?php

namespace RupeshDai\NepaliSmsGateway\Contracts;

interface SmsGatewayInterface
{
    /**Rupe
     * Send SMS message to a phone number
     *
     * @param string $to Recipient phone number
     * @param string $message Message to send
     * @param array $options Additional options for the gateway
     * @return array Response from the gateway
     */
    public function send(string $to, string $message, array $options = []): array;

    /**
     * Send SMS message to multiple phone numbers
     *
     * @param array $recipients Array of recipient phone numbers
     * @param string $message Message to send
     * @param array $options Additional options for the gateway
     * @return array Response from the gateway
     */
    public function sendMultiple(array $recipients, string $message, array $options = []): array;

    /**
     * Check the balance of the account
     *
     * @return array Response containing balance information
     */
    public function checkBalance(): array;

    /**
     * Validate if phone number is in correct format
     *
     * @param string $phoneNumber Phone number to validate
     * @return bool Whether phone number is valid
     */
    public function validatePhoneNumber(string $phoneNumber): bool;
}
