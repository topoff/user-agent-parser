<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\SinergiBrowserDetector;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\SinergiBrowserDetector
 */
class SinergiBrowserDetectorTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getBrowserParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock(\Sinergi\BrowserDetector\Browser::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getOsParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock(\Sinergi\BrowserDetector\Os::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getDeviceParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock(\Sinergi\BrowserDetector\Device::class);
    }

    public function test_get_name(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertEquals('SinergiBrowserDetector', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertEquals('https://github.com/sinergi/php-browser-detector', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertEquals('sinergi/browser-detector', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new SinergiBrowserDetector;

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
                'brand' => false,
                'type' => false,
                'isMobile' => true,
                'isTouch' => false,
            ],

            'bot' => [
                'isBot' => true,
                'name' => false,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new SinergiBrowserDetector;

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');

        /*
         * device model
         */
        $this->assertIsRealResult($provider, false, 'Windows Phone', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Windows Phone something', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'something Windows Phone', 'device', 'model');
    }

    public function test_provider(): void
    {
        $provider = new SinergiBrowserDetector;

        $this->assertInstanceOf(\Sinergi\BrowserDetector\Browser::class, $provider->getBrowserParser(''));
        $this->assertInstanceOf(\Sinergi\BrowserDetector\Os::class, $provider->getOperatingSystemParser(''));
        $this->assertInstanceOf(\Sinergi\BrowserDetector\Device::class, $provider->getDeviceParser(''));
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_value(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('unknown'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_value2(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows Phone'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $deviceParser);

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('SinergiBrowserDetector', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

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
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Chrome'));
        $browserParser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('28.0.1468'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Chrome',
                'version' => [
                    'major' => 28,
                    'minor' => 0,
                    'patch' => 1468,

                    'alias' => null,

                    'complete' => '28.0.1468',
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
        $osParser = $this->getOsParser();
        $osParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows'));
        $osParser->expects($this->any())
            ->method('getVersion')
            ->will($this->returnValue('7.0.1'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $osParser);

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $this->getDeviceParser());

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
        $osParser = $this->getOsParser();
        $osParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(\Sinergi\BrowserDetector\Browser::UNKNOWN));
        $osParser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));
        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('iPad'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $this->getBrowserParser());

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $osParser);

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $deviceParser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPad',
                'brand' => null,
                'type' => null,

                'isMobile' => true,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device - name default
     */
    public function test_parse_device_default_value(): void
    {
        $browserParser = $this->getBrowserParser();
        $browserParser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(false));
        $browserParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Chrome'));

        $deviceParser = $this->getDeviceParser();
        $deviceParser->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('Windows Phone'));

        $provider = new SinergiBrowserDetector;

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('browserParser');
        $property->setValue($provider, $browserParser);

        $property = $reflection->getProperty('osParser');
        $property->setValue($provider, $this->getOsParser());

        $property = $reflection->getProperty('deviceParser');
        $property->setValue($provider, $deviceParser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Chrome',
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],

            'device' => [
                'model' => null,
                'brand' => null,
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
