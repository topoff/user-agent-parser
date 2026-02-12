<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Zsxsoft;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\Zsxsoft
 */
class ZsxsoftTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    private function getParser($returnValue = null): \PHPUnit\Framework\MockObject\MockObject
    {
        $parser = $this->getMockBuilder('UserAgent')
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->addMethods([
                'analyze',
            ])
            ->getMock();

        $parser->data = $returnValue ?? [
            'browser' => [],
            'os' => [],
            'device' => [],
            'platform' => [],
        ];

        return $parser;
    }

    public function test_get_name(): void
    {
        $provider = new Zsxsoft;

        $this->assertEquals('Zsxsoft', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new Zsxsoft;

        $this->assertEquals('https://github.com/zsxsoft/php-useragent', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new Zsxsoft;

        $this->assertEquals('zsxsoft/php-useragent', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new Zsxsoft;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new Zsxsoft;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new Zsxsoft;

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
        $provider = new Zsxsoft;

        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, false, 'UnKnown');
        $this->assertIsRealResult($provider, true, 'Unknown thing');

        $this->assertIsRealResult($provider, false, 'Mozilla Compatible', 'browser', 'name');
        $this->assertIsRealResult($provider, true, 'Mozilla', 'browser', 'name');

        $this->assertIsRealResult($provider, false, 'Browser', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Browser model name', 'device', 'model');

        $this->assertIsRealResult($provider, false, 'Android', 'device', 'model');
        $this->assertIsRealResult($provider, true, 'Android model name', 'device', 'model');
    }

    public function test_parser(): void
    {
        $provider = new Zsxsoft;
        $this->assertInstanceOf('UserAgent', $provider->getParser());

        $parser = $this->getParser();

        $provider = new Zsxsoft($parser);

        $this->assertSame($parser, $provider->getParser());
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new Zsxsoft($this->getParser());

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_browser_name(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $result = [
            'browser' => [
                'name' => 'Mozilla Compatible',
                'version' => '3.2.1',
            ],
            'os' => [],
            'device' => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_device_model(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $result = [
            'browser' => [],
            'os' => [],
            'device' => [
                'model' => 'Android',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $result = [
            'browser' => [
                'name' => 'Firefox',
                'version' => '3.2.1',
            ],
            'os' => [],
            'device' => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Zsxsoft', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Browser only
     */
    public function test_parse_browser(): void
    {
        $result = [
            'browser' => [
                'name' => 'Firefox',
                'version' => '3.2.1',
            ],
            'os' => [],
            'device' => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

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
        $result = [
            'browser' => [],
            'os' => [
                'name' => 'Windows',
                'version' => '7.0.1',
            ],
            'device' => [],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

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
        $result = [
            'browser' => [],
            'os' => [],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

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

    /**
     * Device model only
     */
    public function test_parse_device_model_only(): void
    {
        $result = [
            'browser' => [],
            'os' => [],
            'device' => [
                'model' => 'One+',
            ],
            'platform' => [],
        ];

        $provider = new Zsxsoft($this->getParser($result));

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'One+',
                'brand' => null,
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
