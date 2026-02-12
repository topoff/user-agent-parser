<?php

namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\BrowscapPhp;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 */
class BrowscapPhpTest extends AbstractBrowscapTestCase
{
    public function test_no_result_found_with_warm_cache(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $provider->parse('...');
    }

    public function test_real_result_bot(): void
    {
        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $result = $provider->parse('Mozilla/2.0 (compatible; Ask Jeeves)');
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
                'name' => 'AskJeeves',
                'type' => null,
            ],
        ], $result->toArray());
    }

    public function test_real_result_device(): void
    {
        $provider = new BrowscapPhp($this->getParserWithWarmCache(''));

        $result = $provider->parse('Mozilla/5.0 (SMART-TV; X11; Linux armv7l) AppleWebkit/537.42 (KHTML, like Gecko) Chromium/48.0.1349.2 Chrome/25.0.1349.2 Safari/537.42');
        $this->assertEquals([
            'browser' => [
                'name' => 'Chromium',
                'version' => [
                    'major' => 48,
                    'minor' => null,
                    'patch' => null,

                    'alias' => null,

                    'complete' => '48.0',
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
                'name' => 'Linux',
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
                'type' => 'TV Device',

                'isMobile' => null,
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
