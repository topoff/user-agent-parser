<?php

namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\UAParser;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 */
class UAParserTest extends AbstractProviderTestCase
{
    private function getParser(): \UAParser\Parser
    {
        return new \UAParser\Parser(include __DIR__.'/tests/resources/uaparser/regexes.php');
    }

    public function test_method_parse(): void
    {
        $provider = new UAParser($this->getParser());
        $parser = $provider->getParser();

        /*
         * test method exists
         */
        $class = new \ReflectionClass($parser);

        $this->assertTrue($class->hasMethod('parse'), 'method parse() does not exist anymore');

        /*
         * test paramters
         */
        $method = $class->getMethod('parse');
        $parameters = $method->getParameters();

        $this->assertEquals(2, count($parameters));

        /* @var $optionalPara \ReflectionParameter */
        $optionalPara = $parameters[1];

        $this->assertTrue($optionalPara->isOptional(), '2nd parameter of parse() is not optional anymore');
    }

    public function test_parse_result(): void
    {
        $provider = new UAParser($this->getParser());
        $parser = $provider->getParser();

        /* @var $result \UAParser\Result\Client */
        $result = $parser->parse('A real user agent...');

        $this->assertInstanceOf(\UAParser\Result\Client::class, $result);

        $class = new \ReflectionClass($result);

        $this->assertTrue($class->hasProperty('ua'), 'property ua does not exist anymore');
        $this->assertInstanceOf(\UAParser\Result\UserAgent::class, $result->ua);

        $this->assertTrue($class->hasProperty('os'), 'property os does not exist anymore');
        $this->assertInstanceOf(\UAParser\Result\OperatingSystem::class, $result->os);

        $this->assertTrue($class->hasProperty('device'), 'property os does not exist anymore');
        $this->assertInstanceOf(\UAParser\Result\Device::class, $result->device);
    }

    public function test_class_browser_result(): void
    {
        $class = new \ReflectionClass(\UAParser\Result\OperatingSystem::class);

        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('major'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('minor'), 'property minor does not exist anymore');
        $this->assertTrue($class->hasProperty('patch'), 'property patch does not exist anymore');
    }

    public function test_class_os_result(): void
    {
        $class = new \ReflectionClass(\UAParser\Result\UserAgent::class);

        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('major'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('minor'), 'property minor does not exist anymore');
        $this->assertTrue($class->hasProperty('patch'), 'property patch does not exist anymore');
    }

    public function test_class_device_result(): void
    {
        $class = new \ReflectionClass(\UAParser\Result\Device::class);

        $this->assertTrue($class->hasProperty('model'), 'property family does not exist anymore');
        $this->assertTrue($class->hasProperty('brand'), 'property major does not exist anymore');
        $this->assertTrue($class->hasProperty('family'), 'property family does not exist anymore');
    }

    public function test_no_result_found(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new UAParser;

        $provider->parse('...');
    }

    public function test_real_result_bot(): void
    {
        $provider = new UAParser;

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
                'name' => 'Googlebot',
                'type' => null,
            ],
        ], $result->toArray());

        /*
         * Test the raw result
         */
        $rawResult = $result->getProviderResultRaw();

        $this->assertInstanceOf(\UAParser\Result\Client::class, $rawResult);
        $this->assertObjectHasAttribute('ua', $rawResult);
        $this->assertObjectHasAttribute('os', $rawResult);
        $this->assertObjectHasAttribute('device', $rawResult);
        $this->assertObjectHasAttribute('originalUserAgent', $rawResult);

        // ua
        $ua = $rawResult->ua;
        $this->assertInstanceOf(\UAParser\Result\UserAgent::class, $ua);
        $this->assertObjectHasAttribute('major', $ua);
        $this->assertObjectHasAttribute('minor', $ua);
        $this->assertObjectHasAttribute('patch', $ua);
        $this->assertObjectHasAttribute('family', $ua);

        // os
        $os = $rawResult->os;
        $this->assertInstanceOf(\UAParser\Result\OperatingSystem::class, $os);
        $this->assertObjectHasAttribute('major', $os);
        $this->assertObjectHasAttribute('minor', $os);
        $this->assertObjectHasAttribute('patch', $os);
        $this->assertObjectHasAttribute('patchMinor', $os);
        $this->assertObjectHasAttribute('family', $os);

        // os
        $device = $rawResult->device;
        $this->assertInstanceOf(\UAParser\Result\Device::class, $device);
        $this->assertObjectHasAttribute('brand', $device);
        $this->assertObjectHasAttribute('model', $device);
        $this->assertObjectHasAttribute('family', $device);
    }

    public function test_real_result_device(): void
    {
        $provider = new UAParser;

        $result = $provider->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 5_0 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3');
        $this->assertEquals([
            'browser' => [
                'name' => 'Mobile Safari',
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
                'brand' => 'Apple',
                'type' => null,

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
