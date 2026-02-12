<?php

namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\FiftyOneDegreesCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\FiftyOneDegreesCom
 */
class FiftyOneDegreesComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function test_get_name(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertEquals('FiftyOneDegreesCom', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertEquals('https://51degrees.com', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

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
        $provider = new FiftyOneDegreesCom($this->getClient(), 'apiKey123');

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'Unknown');
        $this->assertIsRealResult($provider, true, 'Unknown something');
        $this->assertIsRealResult($provider, true, 'something Unknown');
    }

    /**
     * Empty user agent
     */
    public function test_parse_no_result_found_exception_empty_user_agent(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $responseQueue = [
            new Response(200),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
    }

    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * No JSON returned
     */
    public function test_parse_request_exception_content_type(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'text/html',
            ], 'something'),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * user_key_invalid
     */
    public function test_parse_invalid_credentials_exception_invalid_key(): void
    {
        $this->expectException(\UserAgentParser\Exception\InvalidCredentialsException::class);

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(403, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * unknown
     */
    public function test_parse_request_exception_unknown(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'None';

        $responseQueue = [
            new Response(500, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    public function test_parse_request_exception_missing_data(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $parseResult = new stdClass;
        $parseResult->IsCrawler = [
            'True',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('FiftyOneDegreesCom', $result->getProviderName());
        $this->assertNull($result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $parseResult = new stdClass;
        $parseResult->IsCrawler = [
            'True',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

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
        $parseResult = new stdClass;
        $parseResult->BrowserName = [
            'Firefox',
        ];
        $parseResult->BrowserVersion = [
            '3.2.1',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

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
     * Engine only
     */
    public function test_parse_engine(): void
    {
        $parseResult = new stdClass;
        $parseResult->LayoutEngine = [
            'Webkit',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'renderingEngine' => [
                'name' => 'Webkit',
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
        $parseResult = new stdClass;
        $parseResult->PlatformName = [
            'BlackBerryOS',
        ];
        $parseResult->PlatformVersion = [
            '6.0.0',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'BlackBerryOS',
                'version' => [
                    'major' => 6,
                    'minor' => 0,
                    'patch' => 0,

                    'alias' => null,

                    'complete' => '6.0.0',
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
        $parseResult = new stdClass;
        $parseResult->HardwareVendor = [
            'Dell',
        ];
        $parseResult->HardwareFamily = [
            'Galaxy Note',
        ];
        $parseResult->DeviceType = [
            'mobile',
        ];
        $parseResult->IsMobile = [
            'True',
        ];

        $rawResult = new stdClass;
        $rawResult->MatchMethod = 'Direct';
        $rawResult->Values = $parseResult;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json; charset=utf-8',
            ], json_encode($rawResult)),
        ];

        $provider = new FiftyOneDegreesCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => 'Galaxy Note',
                'brand' => 'Dell',
                'type' => 'mobile',

                'isMobile' => true,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
