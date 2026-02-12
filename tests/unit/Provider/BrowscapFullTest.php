<?php

namespace UserAgentParserTest\Unit\Provider;

use UserAgentParser\Provider\BrowscapFull;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\BrowscapFull
 */
class BrowscapFullTest extends AbstractProviderTestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getParser(?\stdClass $result = null): \PHPUnit\Framework\MockObject\MockObject
    {
        $cache = self::createMock(\BrowscapPHP\Cache\BrowscapCache::class);
        $cache->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('FULL'));

        $parser = self::createMock(\BrowscapPHP\Browscap::class);
        $parser->expects($this->any())
            ->method('getCache')
            ->will($this->returnValue($cache));
        $parser->expects($this->any())
            ->method('getBrowser')
            ->will($this->returnValue($result));

        return $parser;
    }

    public function test_get_name(): void
    {
        $provider = new BrowscapFull($this->getParser());

        $this->assertEquals('BrowscapFull', $provider->getName());
    }

    public function test_detection_capabilities(): void
    {
        $provider = new BrowscapFull($this->getParser());

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
                'name' => true,
                'version' => true,
            ],

            'device' => [
                'model' => true,
                'brand' => true,
                'type' => true,
                'isMobile' => true,
                'isTouch' => true,
            ],

            'bot' => [
                'isBot' => true,
                'name' => true,
                'type' => true,
            ],
        ], $provider->getDetectionCapabilities());
    }
}
