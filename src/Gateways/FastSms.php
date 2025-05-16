<?php

namespace RupeshDai\NepaliSmsGateway\Gateways;

use RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;
use Illuminate\Support\Facades\Http;

class FastSms implements SmsGatewayInterface
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $sender;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Create a new Fast SMS Gateway instance.
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->apiKey = $config['api_key'] ?? null;
        $this->sender = $config['sender'] ?? null;
        $this->baseUrl = $config['base_url'] ?? 'https://fastsms.com/api/v1/';

        if (empty($this->apiKey) || empty($this->sender)) {
            throw SmsException::configurationError('fast');
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . 'send', [
                'sender' => $options['sender'] ?? $this->sender,
                'recipient' => $to,
                'message' => $message,
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
            throw SmsException::gatewayError('fast', $e->getMessage());
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
            $requestData = [
                'sender' => $options['sender'] ?? $this->sender,
                'recipients' => $recipients,
                'message' => $message,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . 'send-bulk', $requestData);

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
            throw SmsException::gatewayError('fast', $e->getMessage());
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
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->get($this->baseUrl . 'balance');

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
            throw SmsException::gatewayError('fast', $e->getMessage());
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
        // Basic phone number validation logic
        // You may need to adjust this based on your target countries
        return preg_match('/^\+?[0-9]{10,15}$/', $phoneNumber) === 1;
    }
}
