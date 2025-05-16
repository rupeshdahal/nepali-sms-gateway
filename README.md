# Laravel SMS Package

A Laravel package that provides a simple and consistent way to send SMS messages through multiple SMS gateways.

## Currently Supported Gateways
- SparrowSMS
- AkashSMS
- FastSMS

## Installation

You can install the package via composer:

```bash
composer require rupeshdahal/nepali-sms-gateway
```

## Publish Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="RupeshDai\NepaliSmsGateway\Providers\SmsServiceProvider"
```

This will create a `config/sms.php` file in your app.

## Configuration

Set your SMS gateway credentials in your `.env` file:

```
# Default gateway to use
SMS_GATEWAY=sparrow

# Sparrow SMS config
SPARROW_SMS_TOKEN=your_sparrow_token
SPARROW_SMS_FROM=YourSender

# Akash SMS config
AKASH_SMS_AUTH_KEY=your_akash_auth_key
AKASH_SMS_SENDER_ID=YourSenderId

# Fast SMS config
FAST_SMS_API_KEY=your_fast_api_key
FAST_SMS_SENDER=YourSender

# General settings
SMS_LOG_ENABLED=true
SMS_VALIDATE_PHONE_NUMBER=true
```

## Usage

### Basic Usage

To send an SMS using the default gateway:

```php
use RupeshDai\NepaliSmsGateway\Facades\Sms;

// Send a single SMS
$result = Sms::send('9801234567', 'Your message here');

// Send SMS to multiple recipients
$result = Sms::sendMultiple(['9801234567', '9809876543'], 'Your message here');

// Check balance
$balance = Sms::checkBalance();
```

### Specify Gateway

You can specify which gateway to use for a specific SMS:

```php
$result = Sms::send('9801234567', 'Your message here', [], 'sparrow');
$result = Sms::send('9801234567', 'Your message here', [], 'akash');
$result = Sms::send('9801234567', 'Your message here', [], 'fast');
```

### Additional Options

You can pass additional options to the `send` method:

```php
// Override sender ID for this message
$result = Sms::send('9801234567', 'Your message here', [
    'from' => 'CUSTOM'
]);
```

### Error Handling

```php
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;

try {
    $result = Sms::send('9801234567', 'Your message');
    
    if ($result['success']) {
        // Message sent successfully
    } else {
        // Message failed to send
        // $result['error'] contains the error message
    }
} catch (SmsException $e) {
    // Handle exceptions
    echo $e->getMessage();
}
```

## Extending the Package

### Adding a New Gateway

1. Create a new gateway class that implements `SmsGatewayInterface`
2. Add the gateway configuration to the `config/sms.php` file
3. Update the `SmsManager::resolveGatewayClass()` method to include your new gateway

Example:

```php
// Create your gateway class in YourApp\Sms\Gateways\NewGateway.php
namespace YourApp\Sms\Gateways;

use RupeshDai\NepaliSmsGateway\Contracts\SmsGatewayInterface;

class NewGateway implements SmsGatewayInterface
{
    // Implement the required methods
}

// In a service provider, extend the SmsManager
$this->app->extend('sms', function ($manager, $app) {
    $manager->extend('new-gateway', function ($app) {
        return new \YourApp\Sms\Gateways\NewGateway($app['config']['sms.gateways.new-gateway']);
    });
    
    return $manager;
});
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
