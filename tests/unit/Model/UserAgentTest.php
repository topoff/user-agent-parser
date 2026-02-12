<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\UserAgent;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\UserAgent
 */
class UserAgentTest extends TestCase
{
    public function test_browser(): void
    {
        $ua = new UserAgent;

        $this->assertInstanceOf(\UserAgentParser\Model\Browser::class, $ua->getBrowser());

        $mock = self::createMock(\UserAgentParser\Model\Browser::class);
        $ua->setBrowser($mock);
        $this->assertSame($mock, $ua->getBrowser());
    }

    public function test_rendering_engine(): void
    {
        $ua = new UserAgent;

        $this->assertInstanceOf(\UserAgentParser\Model\RenderingEngine::class, $ua->getRenderingEngine());

        $mock = self::createMock(\UserAgentParser\Model\RenderingEngine::class);
        $ua->setRenderingEngine($mock);
        $this->assertSame($mock, $ua->getRenderingEngine());
    }

    public function test_operating_system(): void
    {
        $ua = new UserAgent;

        $this->assertInstanceOf(\UserAgentParser\Model\OperatingSystem::class, $ua->getOperatingSystem());

        $mock = self::createMock(\UserAgentParser\Model\OperatingSystem::class);
        $ua->setOperatingSystem($mock);
        $this->assertSame($mock, $ua->getOperatingSystem());
    }

    public function test_device(): void
    {
        $ua = new UserAgent;

        $this->assertInstanceOf(\UserAgentParser\Model\Device::class, $ua->getDevice());

        $mock = self::createMock(\UserAgentParser\Model\Device::class);
        $ua->setDevice($mock);
        $this->assertSame($mock, $ua->getDevice());
    }

    public function test_bot(): void
    {
        $ua = new UserAgent;

        $this->assertInstanceOf(\UserAgentParser\Model\Bot::class, $ua->getBot());

        $mock = self::createMock(\UserAgentParser\Model\Bot::class);
        $ua->setBot($mock);
        $this->assertSame($mock, $ua->getBot());
    }

    public function test_is_bot(): void
    {
        $ua = new UserAgent;

        $this->assertFalse($ua->isBot());

        $ua->getBot()->setIsBot(false);
        $this->assertFalse($ua->isBot());

        $ua->getBot()->setIsBot(true);
        $this->assertTrue($ua->isBot());
    }

    public function test_is_mobile(): void
    {
        $ua = new UserAgent;

        $this->assertFalse($ua->isMobile());

        $ua->getDevice()->setIsMobile(false);
        $this->assertFalse($ua->isMobile());

        $ua->getDevice()->setIsMobile(true);
        $this->assertTrue($ua->isMobile());
    }

    public function test_provider_result_raw(): void
    {
        $ua = new UserAgent;

        $this->assertNull($ua->getProviderResultRaw());

        $ua->setProviderResultRaw(['test']);
        $this->assertEquals(['test'], $ua->getProviderResultRaw());
    }

    public function test_to_array(): void
    {
        $ua = new UserAgent;

        $this->assertEquals([
            'browser' => $ua->getBrowser()->toArray(),
            'renderingEngine' => $ua->getRenderingEngine()->toArray(),
            'operatingSystem' => $ua->getOperatingSystem()->toArray(),
            'device' => $ua->getDevice()->toArray(),
            'bot' => $ua->getBot()->toArray(),
        ], $ua->toArray());

        $this->assertEquals([
            'browser' => $ua->getBrowser()->toArray(),
            'renderingEngine' => $ua->getRenderingEngine()->toArray(),
            'operatingSystem' => $ua->getOperatingSystem()->toArray(),
            'device' => $ua->getDevice()->toArray(),
            'bot' => $ua->getBot()->toArray(),
            'providerResultRaw' => null,
        ], $ua->toArray(true));
    }
}
