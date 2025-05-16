<?php

namespace RupeshDai\NepaliSmsGateway\Gateways;

use RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;
use Illuminate\Support\Facades\Http;

class AkashSms implements SmsGatewayInterface
{
    /**
     * @var string
     */
    protected $authKey;

    /**
     * @var string
     */
    protected $senderId;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Create a new Akash SMS Gateway instance.
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->authKey = $config['auth_key'] ?? null;
        $this->senderId = $config['sender_id'] ?? null;
        $this->baseUrl = $config['base_url'] ?? 'https://akashsms.com/api/v3/';

        if (empty($this->authKey) || empty($this->senderId)) {
            throw SmsException::configurationError('akash');
        }
    }

    /**
     * Send SMS message to a phone number
     *
     * @param string $to Recipient phone number
     * @param string $message Message to send
     * @param array $options Additional options for the gateway
     * @return array Response from the gateway
     */
    public function send(string $to, string $message, array $options = []): array
    {
        if (!$this->validatePhoneNumber($to)) {
            throw SmsException::invalidPhoneNumber($to);
        }

        try {
            $response = Http::post($this->baseUrl . 'sms/send', [
                'auth_token' => $this->authKey,
                'from' => $options['sender_id'] ?? $this->senderId,
                'to' => $to,
                'text' => $message,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'SMS sent successfully'
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'message' => 'Failed to send SMS'
            ];
        } catch (\Exception $e) {
            throw SmsException::gatewayError('akash', $e->getMessage());
        }
    }

    /**
     * Send SMS message to multiple phone numbers
     *
     * @param array $recipients Array of recipient phone numbers
     * @param string $message Message to send
     * @param array $options Additional options for the gateway
     * @return array Response from the gateway
     */
    public function sendMultiple(array $recipients, string $message, array $options = []): array
    {
        try {
            $response = Http::post($this->baseUrl . 'sms/send', [
                'auth_token' => $this->authKey,
                'from' => $options['sender_id'] ?? $this->senderId,
                'to' => implode(',', $recipients),
                'text' => $message,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'Bulk SMS sent successfully'
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'message' => 'Failed to send bulk SMS'
            ];
        } catch (\Exception $e) {
            throw SmsException::gatewayError('akash', $e->getMessage());
        }
    }

    /**
     * Check the balance of the account
     *
     * @return array Response containing balance information
     */
    public function checkBalance(): array
    {
        try {
            $response = Http::get($this->baseUrl . 'credit', [
                'auth_token' => $this->authKey,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'data' => $result,
                    'message' => 'Balance retrieved successfully'
                ];
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'message' => 'Failed to retrieve balance'
            ];
        } catch (\Exception $e) {
            throw SmsException::gatewayError('akash', $e->getMessage());
        }
    }

    /**
     * Validate if phone number is in correct format
     *
     * @param string $phoneNumber Phone number to validate
     * @return bool Whether phone number is valid
     */
    public function validatePhoneNumber(string $phoneNumber): bool
    {
        // Basic phone number validation for Nepal (typically 10 digits)
        // You may want to enhance this with more specific rules
        return preg_match('/^[9][6-9][0-9]{8}$/', $phoneNumber) === 1;
    }
}
