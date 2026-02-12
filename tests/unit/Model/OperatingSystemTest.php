<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\OperatingSystem;
use UserAgentParser\Model\Version;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\OperatingSystem
 */
class OperatingSystemTest extends TestCase
{
    public function test_name(): void
    {
        $os = new OperatingSystem;

        $this->assertNull($os->getName());

        $name = 'Windows';
        $os->setName($name);
        $this->assertEquals($name, $os->getName());
    }

    public function test_version(): void
    {
        $os = new OperatingSystem;

        $this->assertInstanceOf(\UserAgentParser\Model\Version::class, $os->getVersion());

        $version = new Version;
        $os->setVersion($version);
        $this->assertSame($version, $os->getVersion());
    }

    public function test_to_array(): void
    {
        $os = new OperatingSystem;

        $this->assertEquals([
            'name' => null,
            'version' => $os->getVersion()
                ->toArray(),
        ], $os->toArray());

        $os->setName('Linux');
        $this->assertEquals([
            'name' => 'Linux',
            'version' => $os->getVersion()
                ->toArray(),
        ], $os->toArray());
    }
}
