<?php

namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\JenssegersAgent;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 */
class JenssegersAgentTest extends AbstractProviderTestCase
{
    public function test_no_result_found(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new JenssegersAgent;

        $provider->parse('...');
    }

    public function test_real_result_bot(): void
    {
        $provider = new JenssegersAgent;

        $result = $provider->parse('Googlebot/2.1 (+http://www.googlebot.com/bot.html)');
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
                'name' => 'Google',
                'type' => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();
        $this->assertEquals([
            'browserName' => false,
            'browserVersion' => false,

            'osName' => false,
            'osVersion' => false,

            'deviceModel' => 'Bot',
            'isMobile' => false,

            'isRobot' => true,
            'botName' => 'Google',
        ], $rawResult);
    }

    public function test_real_result_device(): void
    {
        $provider = new JenssegersAgent;

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
                'name' => 'iOS',
                'version' => [
                    'major' => 5,
                    'minor' => 0,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '5_0',
                ],
            ],
            'device' => [
                'model' => null,
                'brand' => null,
                'type' => null,

                'isMobile' => true,
                'isTouch' => null,
            ],
            'bot' => [
                'isBot' => null,
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());
    }
}
