<?php

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */

namespace UserAgentParser\Provider
{

    use UserAgentParserTest\Unit\Provider\DonatjUAParserTest;

    /**
     * This is need to mock the testing!
     *
     * @param  string  $userAgent
     */
    function parse_user_agent($userAgent): array
    {
        return [
            'browser' => DonatjUAParserTest::$browser,
            'version' => DonatjUAParserTest::$version,
        ];
    }
}

namespace UserAgentParserTest\Unit\Provider
{

    use UserAgentParser\Provider\DonatjUAParser;

    /**
     * @covers UserAgentParser\Provider\DonatjUAParser
     */
    class DonatjUAParserTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
    {
        public static $browser;

        public static $version;

        public function test_get_name(): void
        {
            $provider = new DonatjUAParser;

            $this->assertEquals('DonatjUAParser', $provider->getName());
        }

        public function test_get_homepage(): void
        {
            $provider = new DonatjUAParser;

            $this->assertEquals('https://github.com/donatj/PhpUserAgent', $provider->getHomepage());
        }

        public function test_get_package_name(): void
        {
            $provider = new DonatjUAParser;

            $this->assertEquals('donatj/phpuseragentparser', $provider->getPackageName());
        }

        public function test_version(): void
        {
            $provider = new DonatjUAParser;

            $this->assertIsString($provider->getVersion());
        }

        public function test_update_date(): void
        {
            $provider = new DonatjUAParser;

            $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
        }

        public function test_detection_capabilities(): void
        {
            $provider = new DonatjUAParser;

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
            $provider = new DonatjUAParser;

            /*
             * general
             */
            $this->assertIsRealResult($provider, true, 'UNKNOWN something');
        }

        public function test_parse_no_result_found_exception(): void
        {
            $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

            self::$browser = null;
            self::$version = null;

            $provider = new DonatjUAParser;

            $reflection = new \ReflectionClass($provider);
            $property = $reflection->getProperty('functionName');
            $property->setValue($provider, '\UserAgentParser\Provider\parse_user_agent');

            $provider->parse('A real user agent...');
        }

        /**
         * Provider name and version in result?
         */
        public function test_provider_name_and_version_is_in_result(): void
        {
            self::$browser = 'Firefox';
            self::$version = '3.0.1';

            $provider = new DonatjUAParser;

            $reflection = new \ReflectionClass($provider);
            $property = $reflection->getProperty('functionName');
            $property->setValue($provider, '\UserAgentParser\Provider\parse_user_agent');

            $result = $provider->parse('A real user agent...');

            // reset
            self::$browser = null;
            self::$version = null;

            $this->assertEquals('DonatjUAParser', $result->getProviderName());
            $this->assertMatchesRegularExpression('/\d{1,}\.\d{1,}/', $result->getProviderVersion());
        }

        /**
         * Browser only
         */
        public function test_parse_browser(): void
        {
            self::$browser = 'Firefox';
            self::$version = '3.0.1';

            $provider = new DonatjUAParser;

            $reflection = new \ReflectionClass($provider);
            $property = $reflection->getProperty('functionName');
            $property->setValue($provider, '\UserAgentParser\Provider\parse_user_agent');

            $result = $provider->parse('A real user agent...');

            // reset
            self::$browser = null;
            self::$version = null;

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
    }
}
