<?php

namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Psr7\Response;
use stdClass;
use UserAgentParser\Provider\Http\UserAgentApiCom;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;
use UserAgentParserTest\Unit\Provider\RequiredProviderTestInterface;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\UserAgentApiCom
 */
class UserAgentApiComTest extends AbstractProviderTestCase implements RequiredProviderTestInterface
{
    public function test_get_name(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        $this->assertEquals('UserAgentApiCom', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        $this->assertEquals('http://useragentapi.com/', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getPackageName());
    }

    public function test_version(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getVersion());
    }

    public function test_update_date(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        $this->assertNull($provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

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
        $provider = new UserAgentApiCom($this->getClient(), 'apiKey123');

        /*
         * general
         */
        $this->assertIsRealResult($provider, true, 'something UNKNOWN');
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

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('');
    }

    /**
     * 400 - key_invalid
     */
    public function test_parse_invalid_credentials_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\InvalidCredentialsException::class);

        $rawResult = new stdClass;
        $rawResult->error = new stdClass;
        $rawResult->error->code = 'key_invalid';

        $responseQueue = [
            new Response(400, [], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 400 - useragent_invalid
     */
    public function test_parse_request_exception_user_agent_invalid(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $rawResult = new stdClass;
        $rawResult->error = new stdClass;
        $rawResult->error->code = 'useragent_invalid';

        $responseQueue = [
            new Response(400, [], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * 500
     */
    public function test_parse_request_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $responseQueue = [
            new Response(500),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * No result found
     */
    public function test_parse_no_result_found_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $rawResult = new stdClass;
        $rawResult->error = new stdClass;
        $rawResult->error->code = 'useragent_not_found';

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $provider->parse('A real user agent...');
    }

    /**
     * Provider name and version in result?
     */
    public function test_provider_name_and_version_is_in_result(): void
    {
        $data = new stdClass;
        $data->platform_type = 'Bot';
        $data->platform_name = 'Googlebot';

        $rawResult = new stdClass;
        $rawResult->data = $data;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

        $result = $provider->parse('A real user agent...');

        $this->assertEquals('UserAgentApiCom', $result->getProviderName());
        $this->assertNull($result->getProviderVersion());
    }

    /**
     * Bot
     */
    public function test_parse_bot(): void
    {
        $data = new stdClass;
        $data->platform_type = 'Bot';
        $data->platform_name = 'Googlebot';

        $rawResult = new stdClass;
        $rawResult->data = $data;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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
        $data = new stdClass;
        $data->browser_name = 'Firefox';
        $data->browser_version = '3.0.1';

        $rawResult = new stdClass;
        $rawResult->data = $data;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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
        $data = new stdClass;
        $data->engine_name = 'Webkit';
        $data->engine_version = '3.2.1';

        $rawResult = new stdClass;
        $rawResult->data = $data;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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
     * Device only
     */
    public function test_parse_device(): void
    {
        $data = new stdClass;
        $data->platform_type = 'mobile';

        $rawResult = new stdClass;
        $rawResult->data = $data;

        $responseQueue = [
            new Response(200, [
                'Content-Type' => 'application/json',
            ], json_encode($rawResult)),
        ];

        $provider = new UserAgentApiCom($this->getClient($responseQueue), 'apiKey123');

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
