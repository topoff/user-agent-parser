<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Endorphin;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\Endorphin
 */
class EndorphinTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        $parser = self::createMock('EndorphinStudio\Detector\DetectorResult');

        $parser->Browser = self::createMock('EndorphinStudio\Detector\Browser');
        $parser->OS = self::createMock('EndorphinStudio\Detector\OS');
        $parser->Device = self::createMock('EndorphinStudio\Detector\Device');
        $parser->Robot = self::createMock('EndorphinStudio\Detector\Robot');

        return $parser;
    }

    public function test_get_name(): void
    {
        $provider = new Endorphin;

        $this->assertEquals('Endorphin', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new Endorphin;

        $this->assertEquals('https://github.com/endorphin-studio/browser-detector', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new Endorphin;

        $this->assertEquals('endorphin-studio/browser-detector', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new Endorphin;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new Endorphin;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new Endorphin;

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
                'model' => false,
                'brand' => false,
                'type' => true,
                'isMobile' => false,
                'isTouch' => false,
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
        $provider = new Endorphin;

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something');
    }

    public function test_parser(): void
    {
        $provider = new Endorphin;

        $this->assertInstanceOf('EndorphinStudio\Detector\DetectorResult', $provider->getParser(''));
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $parser = $this->getParser();
        $parser->Robot->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Google (Smartphone)'));
        $parser->Robot->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('Search Engine'));

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Endorphin', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parser = $this->getParser();
        $parser->Robot->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Google (Smartphone)'));
        $parser->Robot->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('Search Engine'));

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => 'Google (Smartphone)',
                'type' => 'Search Engine',
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
        $parser->Browser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Firefox'));
        $parser->Browser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('3.2.1'));

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

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
        $parser->OS->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows'));
        $parser->OS->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('7.0.1'));

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

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
        $parser->Device->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('iPhone'));
        $parser->Device->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('mobile'));

        $provider = new Endorphin;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type' => 'mobile',

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
