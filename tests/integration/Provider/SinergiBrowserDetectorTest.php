<?php

namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\SinergiBrowserDetector;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 */
class SinergiBrowserDetectorTest extends AbstractProviderTestCase
{
    public function test_browser_parser(): void
    {
        $provider = new SinergiBrowserDetector;

        $parser = $provider->getBrowserParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
        $this->assertTrue($class->hasMethod('isRobot'), 'method isRobot() does not exist anymore');
    }

    public function test_os_parser(): void
    {
        $provider = new SinergiBrowserDetector;

        $parser = $provider->getOperatingSystemParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
        $this->assertTrue($class->hasMethod('getVersion'), 'method getVersion() does not exist anymore');
        $this->assertTrue($class->hasMethod('isMobile'), 'method isMobile() does not exist anymore');
    }

    public function test_device_parser(): void
    {
        $provider = new SinergiBrowserDetector;

        $parser = $provider->getDeviceParser('something');

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('getName'), 'method getName() does not exist anymore');
    }

    public function test_no_result_found(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new SinergiBrowserDetector;

        $provider->parse('...');
    }

    public function test_real_result_bot(): void
    {
        $provider = new SinergiBrowserDetector;

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
                'name' => null,
                'type' => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertIsArray($rawResult);
        $this->assertCount(3, $rawResult);

        $this->assertArrayHasKey('browser', $rawResult);
        $this->assertArrayHasKey('operatingSystem', $rawResult);
        $this->assertArrayHasKey('device', $rawResult);

        $this->assertInstanceOf(\Sinergi\BrowserDetector\Browser::class, $rawResult['browser']);
        $this->assertObjectHasAttribute('name', $rawResult['browser']);
        $this->assertObjectHasAttribute('version', $rawResult['browser']);
        $this->assertObjectHasAttribute('isRobot', $rawResult['browser']);

        $this->assertInstanceOf(\Sinergi\BrowserDetector\Os::class, $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('name', $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('version', $rawResult['operatingSystem']);
        $this->assertObjectHasAttribute('isMobile', $rawResult['operatingSystem']);

        $this->assertInstanceOf(\Sinergi\BrowserDetector\Device::class, $rawResult['device']);
        $this->assertObjectHasAttribute('name', $rawResult['device']);
    }

    public function test_real_result_device(): void
    {
        $provider = new SinergiBrowserDetector;

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

                    'complete' => '5.0',
                ],
            ],
            'device' => [
                'model' => 'iPhone',
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
