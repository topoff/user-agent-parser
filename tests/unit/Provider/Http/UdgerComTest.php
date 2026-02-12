<?php

namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\UdgerCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\UdgerCom
 */
class UdgerComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function test_get_name(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        $this->assertEquals('UdgerCom', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        $this->assertEquals('https://udger.com/', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

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
                'name' => false,
                'type' => false,
            ],
        ], $provider->getDetectionCapabilities());
    }

    public function test_is_real_result(): void
    {
        $provider = new UdgerCom($this->getClient(), 'apiKey123');

        /*
         * general
         */
        $this->assertIsRealResult($provider, false, 'unknown');
        $this->assertIsRealResult($provider, true, 'unknown something');
        $this->assertIsRealResult($provider, true, 'something unknown');
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

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
    }

    /**
     * 200 - flag 4
     */
    public function test_parse_invalid_credentials_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\InvalidCredentialsException::class);

        $rawResult = new stdClass;
        $rawResult->flag = 4;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 200 - flag 6
     */
    public function test_parse_limitation_exceeded_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\LimitationExceededException::class);

        $rawResult = new stdClass;
        $rawResult->flag = 6;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 200 - flag 99
     */
    public function test_parse_request_exception1(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $rawResult = new stdClass;
        $rawResult->flag = 99;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 500
     */
    public function test_parse_request_exception2(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $responseQueue = [
            new Response(500),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

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

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * No result found
     */
    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $rawResult = new stdClass;
        $rawResult->flag = 3;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Missing data
     */
    public function test_parse_request_exception_no_data(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $rawResult = new stdClass;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $info = new stdClass;
        $info->type = 'Robot';
        $info->ua_family = 'Googlebot';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('UdgerCom', $result->getProviderName());
        $this->assertNull($result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $info = new stdClass;
        $info->type = 'Robot';
        $info->ua_family = 'Googlebot';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

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
        $info = new stdClass;
        $info->ua_family = 'Firefox';
        $info->ua_ver = '3.0.1';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

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
     * Rendering engine only
     */
    public function test_parse_rendering_engine(): void
    {
        $info = new stdClass;
        $info->ua_engine = 'Webkit';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

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
        $info = new stdClass;
        $info->os_family = 'Windows';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'operatingSystem' => [
                'name' => 'Windows',
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
     * Device only
     */
    public function test_parse_device(): void
    {
        $info = new stdClass;
        $info->device_name = 'watch';

        $rawResult = new stdClass;
        $rawResult->info = $info;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UdgerCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $expectedResult = [
            'device' => [
                'model' => null,
                'brand' => null,
                'type' => 'watch',

                'isMobile' => null,
                'isTouch' => null,
            ],
        ];

        $this->assertProviderResult($result, $expectedResult);
    }
}
