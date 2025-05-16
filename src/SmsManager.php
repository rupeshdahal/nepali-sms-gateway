<?php

namespace RupeshDai\NepaliSmsGateway;

use Illuminate\Contracts\Foundation\Application;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;
use RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface;
use RupeshDai\NepaliSmsGateway\Gateways\SparrowSms;
use RupeshDai\NepaliSmsGateway\Gateways\AkashSms;
use RupeshDai\NepaliSmsGateway\Gateways\FastSms;
use Illuminate\Support\Facades\Log;

class SmsManager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved SMS gateways.
     *
     * @var array
     */
    protected $gateways = [];

    /**
     * Create a new SMS manager instance.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Get a SMS gateway instance by name.
     *
     * @param string|null $name
     * @return \RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface
     *
     * @throws \RupeshDai\NepaliSmsGateway\Exceptions\SmsException
     */
    public function gateway(?string $name = null): SmsGatewayInterface
    {
        $name = $name ?: $this->getDefaultGateway();

        if (!isset($this->gateways[$name])) {
            $this->gateways[$name] = $this->resolve($name);
        }

        return $this->gateways[$name];
    }

    /**
     * Send an SMS message.
     *
     * @param string $to
     * @param string $message
     * @param array $options
     * @param string|null $gateway
     * @return array
     */
    public function send(string $to, string $message, array $options = [], ?string $gateway = null): array
    {
        $gateway = $this->gateway($gateway);

        $result = $gateway->send($to, $message, $options);

        if ($this->app['config']['sms.log_enabled']) {
            $this->logMessage($to, $message, $result);
        }

        return $result;
    }

    /**
     * Send SMS messages to multiple recipients.
     *
     * @param array $recipients
     * @param string $message
     * @param array $options
     * @param string|null $gateway
     * @return array
     */
    public function sendMultiple(array $recipients, string $message, array $options = [], ?string $gateway = null): array
    {
        $gateway = $this->gateway($gateway);

        $result = $gateway->sendMultiple($recipients, $message, $options);

        if ($this->app['config']['sms.log_enabled']) {
            $this->logMultipleMessages($recipients, $message, $result);
        }

        return $result;
    }

    /**
     * Check the balance of the account.
     *
     * @param string|null $gateway
     * @return array
     */
    public function checkBalance(?string $gateway = null): array
    {
        return $this->gateway($gateway)->checkBalance();
    }

    /**
     * Validate if phone number is in correct format.
     *
     * @param string $phoneNumber
     * @param string|null $gateway
     * @return bool
     */
    public function validatePhoneNumber(string $phoneNumber, ?string $gateway = null): bool
    {
        return $this->gateway($gateway)->validatePhoneNumber($phoneNumber);
    }

    /**
     * Log a message.
     *
     * @param string $to
     * @param string $message
     * @param array $result
     * @return void
     */
    protected function logMessage(string $to, string $message, array $result): void
    {
        $logLevel = $result['success'] ? 'info' : 'error';

        Log::$logLevel('SMS sent', [
            'to' => $to,
            'message' => $message,
            'result' => $result,
        ]);
    }

    /**
     * Log multiple messages.
     *
     * @param array $recipients
     * @param string $message
     * @param array $result
     * @return void
     */
    protected function logMultipleMessages(array $recipients, string $message, array $result): void
    {
        $logLevel = $result['success'] ? 'info' : 'error';

        Log::$logLevel('Bulk SMS sent', [
            'recipients' => $recipients,
            'message' => $message,
            'result' => $result,
        ]);
    }

    /**
     * Resolve the given gateway.
     *
     * @param string $name
     * @return \RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface
     *
     * @throws \RupeshDai\NepaliSmsGateway\Exceptions\SmsException
     */
    protected function resolve(string $name): SmsGatewayInterface
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw SmsException::configurationError($name);
        }

        $gateway = $this->resolveGatewayClass($name);

        return new $gateway($config);
    }

    /**
     * Resolve the gateway class.
     *
     * @param string $name
     * @return string
     *
     * @throws \RupeshDai\NepaliSmsGateway\Exceptions\SmsException
     */
    protected function resolveGatewayClass(string $name): string
    {
        switch ($name) {
            case 'sparrow':
                return SparrowSms::class;
            case 'akash':
                return AkashSms::class;
            case 'fast':
                return FastSms::class;
            default:
                throw SmsException::unsupportedGateway($name);
        }
    }

    /**
     * Get the configuration for a gateway.
     *
     * @param string $name
     * @return array|null
     */
    protected function getConfig(string $name): ?array
    {
        return $this->app['config']["sms.gateways.{$name}"];
    }

    /**
     * Get the default gateway name.
     *
     * @return string
     */
    protected function getDefaultGateway(): string
    {
        return $this->app['config']['sms.default'];
    }

    /**
     * Dynamically call the default gateway instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $method, array $parameters)
    {
        return $this->gateway()->$method(...$parameters);
    }
}
