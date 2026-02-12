<?php

namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\WhatIsMyBrowserCom;

/**
 * @coversNothing
 */
class WhatIsMyBrowserComTest extends AbstractHttpProviderTestCase
{
    public function test_invalid_credentials(): void
    {
        $this->expectException(\UserAgentParser\Exception\InvalidCredentialsException::class);
        $this->expectExceptionMessage('Your API key "invalid_api_key" is not valid for WhatIsMyBrowserCom');

        $provider = new WhatIsMyBrowserCom($this->getClient(), 'invalid_api_key');

        $provider->parse('...');
    }

    public function test_no_result_found(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        if (! defined('CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new WhatIsMyBrowserCom($this->getClient(), CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY);

        $provider->parse('...');
    }

    public function test_real_result_device(): void
    {
        if (! defined('CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new WhatIsMyBrowserCom($this->getClient(), CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');

        $this->assertEquals([
            'browser' => [
                'name' => 'Safari',
                'version' => [
                    'major' => 5,
                    'minor' => 1,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.1',
                ],
            ],
            'renderingEngine' => [
                'name' => 'WebKit',
                'version' => [
                    'major' => 534,
                    'minor' => 46,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '534.46',
                ],
            ],
            'operatingSystem' => [
                'name' => 'iOS',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
                'brand' => 'Apple',
                'type' => 'mobile',

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => null,
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf('stdClass', $rawResult);
        $this->assertCount(35, (array) $rawResult);

        $this->assertObjectHasAttribute('operating_system_name', $rawResult);
        $this->assertObjectHasAttribute('simple_sub_description_string', $rawResult);
        $this->assertObjectHasAttribute('simple_browser_string', $rawResult);
        $this->assertObjectHasAttribute('browser_version', $rawResult);
        $this->assertObjectHasAttribute('extra_info', $rawResult);

        $this->assertObjectHasAttribute('operating_platform', $rawResult);
        $this->assertObjectHasAttribute('extra_info_table', $rawResult);
        $this->assertObjectHasAttribute('layout_engine_name', $rawResult);
        $this->assertObjectHasAttribute('detected_addons', $rawResult);
        $this->assertObjectHasAttribute('operating_system_flavour_code', $rawResult);

        $this->assertObjectHasAttribute('hardware_architecture', $rawResult);
        $this->assertObjectHasAttribute('operating_system_flavour', $rawResult);
        $this->assertObjectHasAttribute('operating_system_frameworks', $rawResult);
        $this->assertObjectHasAttribute('browser_name_code', $rawResult);
        $this->assertObjectHasAttribute('simple_minor', $rawResult);

        $this->assertObjectHasAttribute('operating_system_version', $rawResult);
        $this->assertObjectHasAttribute('simple_operating_platform_string', $rawResult);
        $this->assertObjectHasAttribute('is_abusive', $rawResult);
        $this->assertObjectHasAttribute('simple_medium', $rawResult);
        $this->assertObjectHasAttribute('layout_engine_version', $rawResult);

        $this->assertObjectHasAttribute('browser_capabilities', $rawResult);
        $this->assertObjectHasAttribute('operating_platform_vendor_name', $rawResult);
        $this->assertObjectHasAttribute('operating_system', $rawResult);
        $this->assertObjectHasAttribute('operating_system_version_full', $rawResult);
        $this->assertObjectHasAttribute('operating_platform_code', $rawResult);

        $this->assertObjectHasAttribute('browser_name', $rawResult);
        $this->assertObjectHasAttribute('operating_system_name_code', $rawResult);
        $this->assertObjectHasAttribute('user_agent', $rawResult);
        $this->assertObjectHasAttribute('simple_major', $rawResult);
        $this->assertObjectHasAttribute('browser_version_full', $rawResult);

        $this->assertObjectHasAttribute('software_type', $rawResult);
        $this->assertObjectHasAttribute('software_sub_type', $rawResult);
        $this->assertObjectHasAttribute('hardware_type', $rawResult);
        $this->assertObjectHasAttribute('hardware_sub_type', $rawResult);

        $this->assertObjectHasAttribute('browser', $rawResult);
    }

    public function test_real_result_bot(): void
    {
        if (! defined('CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new WhatIsMyBrowserCom($this->getClient(), CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY);

        $result = $provider->parse('Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0); 360Spider(compatible; HaosouSpider; http://www.haosou.com/help/help_3_2.html)');

        //                 var_dump($result->getProviderResultRaw());
        //                 exit();

        $this->assertEquals([
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
            'renderingEngine' => [
                'name' => null,
                'version' => [
                    'major' => null,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => null,
                ],
            ],
            'operatingSystem' => [
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
                'type' => null,

                'isMobile' => null,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => true,
                'name' => '360Spider',
                'type' => 'crawler',
            ],
        ], $result->toArray());
    }

    public function test_encode_is_correct(): void
    {
        if (! defined('CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $provider = new WhatIsMyBrowserCom($this->getClient(), CREDENTIALS_WHAT_IS_MY_BROWSER_COM_KEY);

        $userAgent = 'Mozilla/5.0 (Linux; U; Android 3.0.1; en-us; HTC T9299+ For AT&T Build/GRJ22) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1';
        $result = $provider->parse($userAgent);

        $this->assertEquals($userAgent, $result->getProviderResultRaw()->user_agent);
    }
}
