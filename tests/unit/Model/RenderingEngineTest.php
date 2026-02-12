<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\RenderingEngine;
use UserAgentParser\Model\Version;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\RenderingEngine
 */
class RenderingEngineTest extends TestCase
{
    public function test_name(): void
    {
        $engine = new RenderingEngine;

        $this->assertNull($engine->getName());

        $name = 'Webkit';
        $engine->setName($name);
        $this->assertEquals($name, $engine->getName());
    }

    public function test_version(): void
    {
        $engine = new RenderingEngine;

        $this->assertInstanceOf(\UserAgentParser\Model\Version::class, $engine->getVersion());

        $version = new Version;
        $engine->setVersion($version);
        $this->assertSame($version, $engine->getVersion());
    }

    public function test_to_array(): void
    {
        $engine = new RenderingEngine;

        $this->assertEquals([
            'name' => null,
            'version' => $engine->getVersion()
                ->toArray(),
        ], $engine->toArray());

        $engine->setName('Trident');
        $this->assertEquals([
            'name' => 'Trident',
            'version' => $engine->getVersion()
                ->toArray(),
        ], $engine->toArray());
    }
}
