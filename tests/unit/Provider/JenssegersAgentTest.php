<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\JenssegersAgent;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\JenssegersAgent
 */
class JenssegersAgentTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        return self::createMock(\Jenssegers\Agent\Agent::class);
    }

    public function test_get_name(): void
    {
        $provider = new JenssegersAgent;

        $this->assertEquals('JenssegersAgent', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new JenssegersAgent;

        $this->assertEquals('https://github.com/jenssegers/agent', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new JenssegersAgent;

        $this->assertEquals('jenssegers/agent', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new JenssegersAgent;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new JenssegersAgent;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new JenssegersAgent;

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
                'type' => false,
                'isMobile' => true,
                'isTouch' => false,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new JenssegersAgent;

        /*
         * browser name
         */
        $this->assertIsRealResult($provider, false, 'GenericBrowser', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'GenericBrowser something', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'something GenericBrowser', 'browser', 'name');
    }

    public function test_parser(): void
    {
        $provider = new JenssegersAgent;

        $this->assertInstanceOf(\Jenssegers\Agent\Agent::class, $provider->getParser());
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();

        $provider = new JenssegersAgent;

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
        $parser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('robot')
            ->will($this->returnValue('Googlebot'));

        $provider = new JenssegersAgent;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('JenssegersAgent', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isRobot')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('robot')
            ->will($this->returnValue('Googlebot'));

        $provider = new JenssegersAgent;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'bot' => [
                'isBot' => true,
                'name' => 'Googlebot',
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
            ->method('browser')
            ->will($this->returnValue('Firefox'));
        $parser->expects($this->any())
            ->method('version')
            ->will($this->returnValue('3.2.1'));

        $provider = new JenssegersAgent;

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
    public function test_parse_os(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('platform')
            ->will($this->returnValue('Windows'));
        $parser->expects($this->any())
            ->method('version')
            ->will($this->returnValue('7.0.1'));

        $provider = new JenssegersAgent;

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
    public function test_device_only(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));

        $provider = new JenssegersAgent;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type' => null,

                'isMobile' => true,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
