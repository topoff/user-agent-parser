<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Chain;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\Chain
 */
class ChainTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    private ?\PHPUnit\Framework\MockObject\MockObject $provider;

    protected function setUp(): void
    {
        $this->provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);
    }

    protected function tearDown(): void
    {
        $this->provider = null;
    }

    public function test_provider(): void
    {
        $chain = new Chain;

        $this->assertIsArray($chain->getProviders());
        $this->assertCount(0, $chain->getProviders());

        $chain = new Chain([
            $this->provider,
        ]);

        $this->assertIsArray($chain->getProviders());
        $this->assertCount(1, $chain->getProviders());
        $this->assertSame([
            $this->provider,
        ], $chain->getProviders());
    }

    public function test_get_name(): void
    {
        $chain = new Chain;

        $this->assertEquals('Chain', $chain->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new Chain;

        $this->assertNull($provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new Chain;

        $this->assertNull($provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new Chain;

        $this->assertNull($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new Chain;

        $this->assertNull($provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new Chain;

        $this->assertEquals([

            'browser' => [
                'name' => false,
                'version' => false,
            ],

            'renderingEngine' => [
                'name' => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name' => false,
                'version' => false,
            ],

            'device' => [
                'model' => false,
                'brand' => false,
                'type' => false,
                'isMobile' => false,
                'isTouch' => false,
            ],

            'bot' => [
                'isBot' => false,
                'name' => false,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new Chain;

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something UNKNOWN');
    }

    /**
     * @todo should throw another exception! since no provider was provided!
     */
    public function test_parse_no_provider_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $chain = new Chain;

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $chain->parse($userAgent);
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = $this->provider;
        $provider->expects($this->any())
            ->method('parse')
            ->will($this->throwException(new \UserAgentParser\Exception\NoResultFoundException));

        $chain = new Chain([
            $provider,
        ]);

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $chain->parse($userAgent);
    }

    public function test_parse_with_provider_and_valid_result(): void
    {
        $resultMock = self::createMock(\UserAgentParser\Model\UserAgent::class);

        $provider = $this->provider;
        $provider->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($resultMock));

        $chain = new Chain([
            $provider,
        ]);

        $userAgent = 'Googlebot/2.1 (http://www.googlebot.com/bot.html)';

        $this->assertSame($resultMock, $chain->parse($userAgent));
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $resultMock = self::createMock(\UserAgentParser\Model\UserAgent::class);
        $resultMock->expects($this->any())
            ->method('getProviderName')
            ->will($this->returnValue('SomeProvider'));
        $resultMock->expects($this->any())
            ->method('getProviderVersion')
            ->will($this->returnValue('1.2'));

        $provider = $this->provider;
        $provider->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($resultMock));

        new Chain([
            $provider,
        ]);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('SomeProvider', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }
}
