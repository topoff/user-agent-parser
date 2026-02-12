<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\Woothee;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Woothee
 */
class WootheeTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(array $returnValue = []): \PHPUnit\Framework\MockObject\MockObject
    {
        $parser = self::createMock(\Woothee\Classifier::class);
        $parser->expects($this->any())
            ->method('parse')
            ->will($this->returnValue($returnValue));

        return $parser;
    }

    public function test_get_name(): void
    {
        $provider = new Woothee;

        $this->assertEquals('Woothee', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new Woothee;

        $this->assertEquals('https://github.com/woothee/woothee-php', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new Woothee;

        $this->assertEquals('woothee/woothee', $provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new Woothee;

        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new Woothee;

        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new Woothee;

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
                'name' => false,
                'version' => false,
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
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new Woothee;

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'UNKNOWN');
        $this->assertIsRealResult($provider, true, 'UNKNOWN something');
        $this->assertIsRealResult($provider, true, 'something UNKNOWN');

        /*
         * device type
         */
        $this->assertIsRealResult($provider, false, 'misc', 'device', 'type');
        $this->assertIsRealResult($provider, true, 'misc something', 'device', 'type');
        $this->assertIsRealResult($provider, true, 'something misc', 'device', 'type');

        /*
         * bot name
         */
        $this->assertIsRealResult($provider, false, 'misc crawler', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'misc crawler something', 'bot', 'name');
        $this->assertIsRealResult($provider, true, 'something misc crawler', 'bot', 'name');
    }

    public function test_parser(): void
    {
        $provider = new Woothee;

        $this->assertInstanceOf(\Woothee\Classifier::class, $provider->getParser());
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser();

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_browser_name(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser([
            'name' => 'UNKNOWN',
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $provider->parse('A real user agent...');
    }

    public function test_parse_no_result_found_exception_default_device_type(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $parser = $this->getParser([
            'category' => 'misc',
        ]);

        $provider = new Woothee;

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
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_CRAWLER,
            'name' => 'Googlebot',
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('Woothee', $result->getProviderName());
        $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_CRAWLER,
            'name' => 'Googlebot',
        ]);

        $provider = new Woothee;

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
     * Bot
     */
    public function test_parse_bot_default_value(): void
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_CRAWLER,
            'name' => 'misc crawler',
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

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
        $parser = $this->getParser([
            'name' => 'Firefox',
            'version' => '3.0.1',
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => 'Firefox',
                'version' => [
                    'major' => 3,
                    'minor' => 0,
                    'patch' => 1,

                    'alias' => null,

                    'complete' => '3.0.1',
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
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_SMARTPHONE,
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type' => 'smartphone',

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }

    /**
     * Device only
     */
    public function test_parse_device_mobilephone(): void
    {
        $parser = $this->getParser([
            'category' => \Woothee\DataSet::DATASET_CATEGORY_MOBILEPHONE,
            'name' => \Woothee\DataSet::VALUE_UNKNOWN,
        ]);

        $provider = new Woothee;

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('parser');
        $property->setValue($provider, $parser);

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'browser' => [
                'name' => null,
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
                'type' => 'mobilephone',

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
