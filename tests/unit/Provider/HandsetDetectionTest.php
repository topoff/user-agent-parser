<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\HandsetDetection;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\HandsetDetection
 */
class HandsetDetectionTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock('HandsetDetection\HD4');
    }

    public function test_get_name(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('HandsetDetection', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('https://github.com/HandsetDetection/php-apikit', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals('handsetdetection/php-apikit', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new HandsetDetection($this->getParser());

        $this->assertEquals([

            'browser' => [
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => false,
                'version' => false,
            ],

            'operatingSystem' => [
                'name' => true,
                'version' => true,
            ],

            'device' => [
                'model' => true,
                'brand' => true,
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
        $provider = new HandsetDetection($this->getParser());

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something');

        $this->assertIsRealResult($provider, false, 'generic', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'generic something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something generic', 'device', 'model');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'analyzer', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'analyzer something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something analyzer', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'bot', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'bot something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something bot', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'crawler', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'crawler something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something crawler', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'library', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'library something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something library', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'spider', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'spider something', 'device', 'model');
        $this->assertIsRealResult($provider, false, 'something spider', 'device', 'model');
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(false);

        $provider = new HandsetDetection($parser);

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_value(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
                'hd_specs' => [
                    'general_browser' => 'generic',
                ],
            ]);

        $provider = new HandsetDetection($parser);

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
                'hd_specs' => [
                    'general_browser' => 'Firefox',
                    'general_browser_version' => '3.2.1',
                ],
            ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('HandsetDetection', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Browser only
     */
    public function test_parse_browser(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
                'hd_specs' => [
                    'general_browser' => 'Firefox',
                    'general_browser_version' => '3.2.1',
                ],
            ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 2,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.2.1',
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
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
                'hd_specs' => [
                    'general_platform' => 'Windows',
                    'general_platform_version' => '7.0.1',
                ],
            ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
                'version' => [
                    'major' => 7,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '7.0.1',
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
            ->method('deviceDetect')
            ->willReturn(true);
        $parser->expects($this->any())
            ->method('getReply')
            ->willReturn([
                'hd_specs' => [
                    'general_model' => 'iPhone',
                    'general_vendor' => 'Apple',
                ],
            ]);

        $provider = new HandsetDetection($parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
