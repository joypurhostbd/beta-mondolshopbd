<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Customer\Domain\Contracts\SmsServiceInterface;
use Modules\Customer\Infrastructure\Adapters\Sms\AlphaSmsAdapter;
use Modules\Customer\Infrastructure\Adapters\Sms\GreenwebSmsAdapter;
use Modules\Customer\Infrastructure\Adapters\Sms\LogSmsAdapter;
use Modules\Customer\Infrastructure\Adapters\Sms\SmsGatewayManager;
use Tests\TestCase;

class SmsServiceAdapterTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_sms_adapter_records_messages(): void
    {
        $adapter = new LogSmsAdapter();
        $this->assertTrue($adapter->send('01711223344', 'Welcome!'));
        $this->assertTrue($adapter->sendOtp('01711223344', 543210));

        $messages = $adapter->getSentMessages();
        $this->assertCount(2, $messages);
        $this->assertEquals('01711223344', $messages[0]['to']);
    }

    public function test_sms_gateway_adapters_implement_contract(): void
    {
        $gw = new GreenwebSmsAdapter('dummy_token');
        $this->assertInstanceOf(SmsServiceInterface::class, $gw);

        $alpha = new AlphaSmsAdapter('dummy_key');
        $this->assertInstanceOf(SmsServiceInterface::class, $alpha);

        $joypurhost = new \Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter('dummy_key', '8809612000000');
        $this->assertInstanceOf(SmsServiceInterface::class, $joypurhost);

        $custom = new \Modules\Customer\Infrastructure\Adapters\Sms\CustomSmsAdapter('https://example.com/api', 'key', 'sender');
        $this->assertInstanceOf(SmsServiceInterface::class, $custom);
    }

    public function test_sms_gateway_manager_resolves_and_dispatches(): void
    {
        $service = $this->app->make(SmsServiceInterface::class);
        $this->assertInstanceOf(SmsServiceInterface::class, $service);
        $this->assertInstanceOf(SmsGatewayManager::class, $service);
        $this->assertInstanceOf(LogSmsAdapter::class, $service->getActiveDriver());

        $this->assertTrue($service->send('01711223344', 'Hello via Manager'));
        $this->assertTrue($service->sendOtp('01711223344', 987654));
    }

    public function test_sms_gateway_manager_resolves_joypurhost_and_custom_drivers(): void
    {
        \App\Models\SmsGateway::create([
            'provider' => 'joypurhost',
            'url' => 'http://bulksmsbd.net/api/smsapi',
            'api_key' => 'jh_key',
            'serderid' => '8809612000000',
            'status' => 1,
        ]);

        $manager = new SmsGatewayManager();
        $this->assertInstanceOf(\Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter::class, $manager->getActiveDriver());

        \App\Models\SmsGateway::query()->delete();
        \App\Models\SmsGateway::create([
            'provider' => 'custom',
            'url' => 'https://custom-api.com/send',
            'api_key' => 'custom_key',
            'serderid' => 'MySender',
            'status' => 1,
        ]);

        $customManager = new SmsGatewayManager();
        $this->assertInstanceOf(\Modules\Customer\Infrastructure\Adapters\Sms\CustomSmsAdapter::class, $customManager->getActiveDriver());
    }

    public function test_joypurhost_sms_adapter_sends_with_official_payload_and_202_success(): void
    {
        Http::fake([
            'https://sms.joypurhost.com/api/smsapi*' => Http::response(['response_code' => 202, 'success_message' => 'SMS Submitted Successfully'], 200),
        ]);

        $adapter = new \Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter(
            'jh_test_key',
            '8809612000000'
        );

        $result = $adapter->send('01711223344', 'Your order #123 has been placed');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sms.joypurhost.com/api/smsapi'
                && $request['api_key'] === 'jh_test_key'
                && $request['senderid'] === '8809612000000'
                && $request['number'] === '8801711223344'
                && $request['message'] === 'Your order #123 has been placed';
        });
    }

    public function test_joypurhost_sms_adapter_handles_rejection_code(): void
    {
        Http::fake([
            'https://sms.joypurhost.com/api/smsapi*' => Http::response(['response_code' => 1007, 'error_message' => ['Balance Insufficient']], 200),
        ]);

        $adapter = new \Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter(
            'jh_test_key',
            '8809612000000'
        );

        $result = $adapter->send('01711223344', 'Test Message');

        $this->assertFalse($result);
    }

    public function test_joypurhost_sms_adapter_fetches_credit_balance(): void
    {
        Http::fake([
            'https://sms.joypurhost.com/api/getBalanceApi*' => Http::response(['response_code' => 202, 'balance' => 375.49], 200),
        ]);

        $adapter = new \Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter(
            'jh_test_key',
            '8809612000000'
        );

        $balance = $adapter->getBalance();

        $this->assertEquals('375.49', $balance);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://sms.joypurhost.com/api/getBalanceApi'
                && $request['api_key'] === 'jh_test_key';
        });
    }

    public function test_joypurhost_sms_adapter_test_connection(): void
    {
        Http::fake([
            'https://sms.joypurhost.com/api/getBalanceApi*' => Http::response(['response_code' => 202, 'balance' => 375.49], 200),
        ]);

        $adapter = new \Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter(
            'jh_test_key',
            '8809612000000'
        );

        $res = $adapter->testConnection();

        $this->assertTrue($res['success']);
        $this->assertEquals('375.49', $res['current_balance']);
        $this->assertStringContainsString('375.49', $res['message']);
    }
}