<?php

namespace UserAgentParserTest\Unit\Provider;

use DeviceDetector\DeviceDetector;
use UserAgentParser\Provider\MatomoDeviceDetector;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\MatomoDeviceDetector
 */
class MatomoDeviceDetectorTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock(\DeviceDetector\DeviceDetector::class);
    }

    public function test_get_name(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertEquals('MatomoDeviceDetector', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertEquals('https://github.com/matomo-org/device-detector', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertEquals('matomo/device-detector', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new MatomoDeviceDetector;

        $this->assertEquals([

            'browser' => [
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => true,
                'version' => false,
            ],

            'operatingSystem' => [
                'name' => true,
                'version' => true,
            ],

            'device' => [
                'model' => true,
                'brand' => true,
                'type' => true,
                'isMobile' => true,
                'isTouch' => true,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => true,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new MatomoDeviceDetector;

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'UNK');
        $this->assertIsRealResult($provider, true, 'UNK something');
        $this->assertIsRealResult($provider, true, 'something UNK');

        /*
         * bot name
         */
        $this->assertIsRealResult($provider, false, 'Bot', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Bot something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Bot', 'bot', 'name');

        $this->assertIsRealResult($provider, false, 'Generic Bot', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'Generic Bot something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something Generic Bot', 'bot', 'name');
    }

    public function test_parser(): void
    {
        $provider = new MatomoDeviceDetector;
        $this->assertInstanceOf(\DeviceDetector\DeviceDetector::class, $provider->getParser());

        $parser = $this->getParser();

        $provider = new MatomoDeviceDetector($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();

        $provider = new MatomoDeviceDetector($parser);

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_value(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([
                'name' => 'UNK',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue([
                'name' => 'Hatena RSS',
                'category' => 'something',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('MatomoDeviceDetector', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue([
                'name' => 'Hatena RSS',
                'category' => 'something',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => 'Hatena RSS',
                'type' => 'something',
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - name default
     */
    public function test_parse_bot_name_default(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue([
                'name' => 'Bot',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => null,
                'type' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Bot - name default
     */
    public function test_parse_bot_name_default2(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isBot')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getBot')
            ->will($this->returnValue([
                'name' => 'Generic Bot',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => null,
                'type' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Browser only
     */
    public function test_parse_browser(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([
                'name' => 'Firefox',
                'version' => '3.0',
                'engine' => 'WebKit',
            ]));
        $parser->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue([]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '3.0',
                ],
            ],

            'renderingEngine' => [
                'name' => 'WebKit',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * OS only
     */
    public function test_parse_operating_system(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([
                'engine' => DeviceDetector::UNKNOWN,
            ]));
        $parser->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue([
                'name' => 'Windows',
                'version' => '7.0',
            ]));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '7.0',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function test_parse_device(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue([]));
        $parser->expects($this->any())
            ->method('getOs')
            ->will($this->returnValue([]));

        $parser->expects($this->any())
            ->method('getDevice')
            ->will($this->returnValue(1));

        $parser->expects($this->any())
            ->method('getModel')
            ->will($this->returnValue('iPhone'));
        $parser->expects($this->any())
            ->method('getBrandName')
            ->will($this->returnValue('Apple'));
        $parser->expects($this->any())
            ->method('getDeviceName')
            ->will($this->returnValue('smartphone'));

        $parser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('isTouchEnabled')
            ->will($this->returnValue(true));

        $provider = new MatomoDeviceDetector($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => 'smartphone',

                'isMobile' => true,
                'isTouch' => true,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
