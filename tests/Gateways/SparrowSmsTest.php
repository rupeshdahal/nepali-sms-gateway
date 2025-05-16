<?php

namespace RupeshDai\NepaliSmsGateway\Tests\Gateways;

use Illuminate\Support\Facades\Http;
use RupeshDai\NepaliSmsGateway\Tests\TestCase;
use RupeshDai\NepaliSmsGateway\Gateways\SparrowSms;
use RupeshDai\NepaliSmsGateway\Exceptions\SmsException;

class SparrowSmsTest extends TestCase
{
    protected SparrowSms $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new SparrowSms(config('sms.gateways.sparrow'));

        Http::fake([
            'api.sparrowsms.com/v2/sms/*' => Http::response(['response_code' => 200, 'response' => 'success'], 200),
            'api.sparrowsms.com/v2/credit/*' => Http::response(['balance' => 1000], 200),
        ]);
    }

    /** @test */
    public function it_throws_exception_when_not_configured()
    {
        $this->expectException(SmsException::class);

        new SparrowSms([]);
    }

    /** @test */
    public function it_can_send_sms()
    {
        $result = $this->gateway->send('9801234567', 'Test message');

        $this->assertTrue($result['success']);
        $this->assertEquals('SMS sent successfully', $result['message']);
    }

    /** @test */
    public function it_can_send_multiple_sms()
    {
        $result = $this->gateway->sendMultiple(['9801234567', '9809876543'], 'Bulk test message');

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['data']['total']);
    }

    /** @test */
    public function it_can_check_balance()
    {
        $result = $this->gateway->checkBalance();

        $this->assertTrue($result['success']);
        $this->assertEquals('Balance retrieved successfully', $result['message']);
    }

    /** @test */
    public function it_validates_phone_number()
    {
        $this->assertTrue($this->gateway->validatePhoneNumber('9801234567'));
        $this->assertFalse($this->gateway->validatePhoneNumber('123'));
    }

    /** @test */
    public function it_throws_exception_for_invalid_phone_number()
    {
        $this->expectException(SmsException::class);

        $this->gateway->send('123', 'Invalid number test');
    }
}
