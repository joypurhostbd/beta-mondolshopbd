<?php

namespace Tests\Unit;

use App\Models\SmsGateway;
use App\Models\SmsProviderJoypurhost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Modules\Customer\Infrastructure\Adapters\Sms\JoypurHostSmsAdapter;
use Tests\TestCase;

class SmsProviderJoypurhostTest extends TestCase
{
    use RefreshDatabase;

    public function test_joypurhost_model_enforces_global_scope_and_default_url(): void
    {
        $jh = SmsProviderJoypurhost::getOrCreate();

        $this->assertEquals(SmsGateway::PROVIDER_JOYPURHOST, $jh->provider);
        $this->assertEquals(JoypurHostSmsAdapter::DEFAULT_URL, $jh->url);

        // Verify that custom provider is not returned by SmsProviderJoypurhost
        SmsGateway::create([
            'provider' => SmsGateway::PROVIDER_CUSTOM,
            'url' => 'https://custom.test',
        ]);

        $this->assertCount(1, SmsProviderJoypurhost::all());
        $this->assertEquals($jh->id, SmsProviderJoypurhost::first()->id);
    }

    public function test_joypurhost_model_to_adapter_and_balance(): void
    {
        Http::fake([
            'https://sms.joypurhost.com/api/getBalanceApi*' => Http::response([
                'response_code' => 202,
                'balance' => 250.00,
            ], 200),
        ]);

        $jh = SmsProviderJoypurhost::getOrCreate();
        $jh->api_key = 'test_key_123';
        $jh->sender_id = '8809612000000';
        $jh->save();

        $adapter = $jh->toAdapter();
        $this->assertInstanceOf(JoypurHostSmsAdapter::class, $adapter);

        $balance = $jh->getBalance();
        $this->assertEquals('250', $balance);

        $testResult = $jh->testConnection();
        $this->assertTrue($testResult['success']);
        $this->assertEquals('250', $testResult['current_balance']);
    }

    public function test_sms_gateway_factory_helpers(): void
    {
        $jh = SmsGateway::joypurhost();
        $this->assertInstanceOf(SmsProviderJoypurhost::class, $jh);
        $this->assertEquals(SmsGateway::PROVIDER_JOYPURHOST, $jh->provider);

        $custom = SmsGateway::custom();
        $this->assertInstanceOf(SmsGateway::class, $custom);
        $this->assertEquals(SmsGateway::PROVIDER_CUSTOM, $custom->provider);
    }
}
