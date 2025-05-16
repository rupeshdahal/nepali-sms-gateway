<?php

namespace RupeshDai\NepaliSmsGateway\Tests;

use Illuminate\Support\Facades\Http;
use RupeshDai\NepaliSmsGateway\Facades\Sms;
use RupeshDai\NepaliSmsGateway\SmsManager;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;

class SmsManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock HTTP responses
        Http::fake([
            'api.sparrowsms.com/v2/sms/*' => Http::response(['response_code' => 200, 'response' => 'success'], 200),
            'api.sparrowsms.com/v2/credit/*' => Http::response(['balance' => 1000], 200),
            'akashsms.com/api/v3/sms/send' => Http::response(['status' => 'success', 'message_id' => '123456'], 200),
            'akashsms.com/api/v3/credit' => Http::response(['balance' => 2000], 200),
            'fastsms.com/api/v1/send' => Http::response(['status' => true, 'message_id' => 'abc123'], 200),
            'fastsms.com/api/v1/send-bulk' => Http::response(['status' => true, 'messages' => 2], 200),
            'fastsms.com/api/v1/balance' => Http::response(['balance' => 1500], 200),
        ]);
    }

    /** @test */
    public function it_can_get_default_gateway()
    {
        $manager = app(SmsManager::class);
        $gateway = $manager->gateway();

        $this->assertInstanceOf(\RupeshDai\NepaliSmsGateway\Gateways\SparrowSms::class, $gateway);
    }

    /** @test */
    public function it_can_get_specific_gateway()
    {
        $manager = app(SmsManager::class);

        $sparrowGateway = $manager->gateway('sparrow');
        $akashGateway = $manager->gateway('akash');
        $fastGateway = $manager->gateway('fast');

        $this->assertInstanceOf(\RupeshDai\NepaliSmsGateway\Gateways\SparrowSms::class, $sparrowGateway);
        $this->assertInstanceOf(\RupeshDai\NepaliSmsGateway\Gateways\AkashSms::class, $akashGateway);
        $this->assertInstanceOf(\RupeshDai\NepaliSmsGateway\Gateways\FastSms::class, $fastGateway);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_gateway()
    {
        $this->expectException(SmsException::class);

        $manager = app(SmsManager::class);
        $manager->gateway('unsupported');
    }

    /** @test */
    public function it_can_send_sms_with_default_gateway()
    {
        $result = Sms::send('9801234567', 'Test message');

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_send_sms_with_specific_gateway()
    {
        $result = Sms::send('9801234567', 'Test with Akash', [], 'akash');

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_send_multiple_sms()
    {
        $result = Sms::sendMultiple(['9801234567', '9809876543'], 'Bulk test');

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_check_balance()
    {
        $result = Sms::checkBalance();

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('balance', $result['data']);
    }

    /** @test */
    public function it_can_validate_phone_number()
    {
        $valid = Sms::validatePhoneNumber('9801234567');
        $invalid = Sms::validatePhoneNumber('123');

        $this->assertTrue($valid);
        $this->assertFalse($invalid);
    }
}
