<?php

namespace RupeshDai\NepaliSmsGateway\Gateways;

use RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;
use Illuminate\Support\Facades\Http;

class SparrowSms implements SmsGatewayInterface
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * Create a new Sparrow SMS Gateway instance.
     *
     * @param array $config
     * @return void
     */
    public function __construct(array $config)
    {
        $this->token = $config['token'] ?? null;
        $this->from = $config['from'] ?? null;
        $this->baseUrl = $config['base_url'] ?? 'http://api.sparrowsms.com/v2/sms/';

        if (empty($this->token) || empty($this->from)) {
            throw SmsException::configurationError('sparrow');
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
            $response = Http::get($this->baseUrl, [
                'token' => $this->token,
                'from' => $options['from'] ?? $this->from,
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
            throw SmsException::gatewayError('sparrow', $e->getMessage());
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
        $results = [];
        $success = 0;
        $failed = 0;

        foreach ($recipients as $recipient) {
            try {
                $result = $this->send($recipient, $message, $options);
                $results[$recipient] = $result;

                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                }
            } catch (\Exception $e) {
                $results[$recipient] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $failed++;
            }
        }

        return [
            'success' => $failed === 0,
            'data' => [
                'total' => count($recipients),
                'success' => $success,
                'failed' => $failed,
                'results' => $results
            ],
            'message' => "{$success} messages sent successfully, {$failed} failed"
        ];
    }

    /**
     * Check the balance of the account
     *
     * @return array Response containing balance information
     */
    public function checkBalance(): array
    {
        try {
            $response = Http::get('http://api.sparrowsms.com/v2/credit/', [
                'token' => $this->token,
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
            throw SmsException::gatewayError('sparrow', $e->getMessage());
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
