<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\WhichBrowser;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\WhichBrowser
 */
class WhichBrowserTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(): \PHPUnit\Framework\MockObject\MockObject
    {
        $parser = self::createMock(\WhichBrowser\Parser::class);

        $parser->browser = new \WhichBrowser\Model\Browser;
        $parser->engine = new \WhichBrowser\Model\Engine;
        $parser->os = new \WhichBrowser\Model\Os;
        $parser->device = new \WhichBrowser\Model\Device;

        return $parser;
    }

    public function test_get_name(): void
    {
        $provider = new WhichBrowser;

        $this->assertEquals('WhichBrowser', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new WhichBrowser;

        $this->assertEquals('https://github.com/WhichBrowser/Parser', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new WhichBrowser;

        $this->assertEquals('whichbrowser/parser', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new WhichBrowser;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new WhichBrowser;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new WhichBrowser;

        $this->assertEquals([

            'browser' => [
                'name' => true,
                'version' => true,
            ],

            'renderingEngine' => [
                'name' => true,
                'version' => true,
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
        $provider = new WhichBrowser;

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something');
    }

    public function test_parser(): void
    {
        $provider = new WhichBrowser;

        $this->assertInstanceOf(\WhichBrowser\Parser::class, $provider->getParser([]));
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();

        $provider = new WhichBrowser;

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
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bot'));
        $parser->browser = new \WhichBrowser\Model\Browser([
            'name' => 'Googlebot',
        ]);

        $provider = new WhichBrowser;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('WhichBrowser', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('bot'));
        $parser->browser = new \WhichBrowser\Model\Browser([
            'name' => 'Googlebot',
        ]);

        $provider = new WhichBrowser;

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
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->browser = new \WhichBrowser\Model\Browser([
            'name' => 'Firefox',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '3.2.1',
            ]),
        ]);

        $provider = new WhichBrowser;

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
     * Browser only "using"
     */
    public function test_parse_browser_using(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $using = new \WhichBrowser\Model\Using([
            'name' => 'Another',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '4.7.3',
            ]),
        ]);

        $parser->browser = new \WhichBrowser\Model\Browser([
            'using' => $using,
        ]);

        $provider = new WhichBrowser;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Another',
                'version' => [
                    'major' => 4,
                    'minor' => 7,
                    'patch' => 3,

                    'alias' => null,

                    'complete' => '4.7.3',
                ],
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Rendering engine only
     */
    public function test_parse_rendering_engine(): void
    {
        $parser = $this->getParser();
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->engine = new \WhichBrowser\Model\Engine([
            'name' => 'Webkit',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '3.2.1',
            ]),
        ]);

        $provider = new WhichBrowser;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name' => 'Webkit',
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
            ->method('isDetected')
            ->will($this->returnValue(true));

        $parser->os = new \WhichBrowser\Model\Os([
            'name' => 'Windows',
            'version' => new \WhichBrowser\Model\Version([
                'value' => '7.0.1',
            ]),
        ]);

        $provider = new WhichBrowser;

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
        $parser->expects($this->any())
            ->method('isDetected')
            ->will($this->returnValue(true));
        $parser->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('watch'));
        $parser->expects($this->any())
            ->method('isMobile')
            ->will($this->returnValue(true));

        $parser->device = new \WhichBrowser\Model\Device([
            'identified' => true,
            'model' => 'iPhone',
            'manufacturer' => 'Apple',
            'type' => 'watch',
        ]);

        $parser->expects($this->any())
            ->method('isType')
            ->will($this->returnValue(true));

        $provider = new WhichBrowser;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => 'watch',

                'isMobile' => true,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
