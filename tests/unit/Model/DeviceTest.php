<?php

namespace UserAgentParserTest;

use PHPUnit\Framework\TestCase;
use UserAgentParser\Model\Device;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Model\Device
 */
class DeviceTest extends TestCase
{
    public function test_model(): void
    {
        $device = new Device;

        $this->assertNull($device->getModel());

        $name = 'OnePlus';
        $device->setModel($name);
        $this->assertEquals($name, $device->getModel());
    }

    public function test_brand(): void
    {
        $device = new Device;

        $this->assertNull($device->getBrand());

        $name = 'Apple';
        $device->setBrand($name);
        $this->assertEquals($name, $device->getBrand());
    }

    public function test_type(): void
    {
        $device = new Device;

        $this->assertNull($device->getType());

        $name = 'mobilephone';
        $device->setType($name);
        $this->assertEquals($name, $device->getType());
    }

    public function test_is_mobile(): void
    {
        $device = new Device;

        $this->assertNull($device->getIsMobile());

        $device->setIsMobile(true);
        $this->assertTrue($device->getIsMobile());
    }

    public function test_is_touch(): void
    {
        $device = new Device;

        $this->assertNull($device->getIsTouch());

        $device->setIsTouch(true);
        $this->assertTrue($device->getIsTouch());
    }

    public function test_to_array(): void
    {
        $device = new Device;

        $this->assertEquals([
            'model' => null,
            'brand' => null,
            'type' => null,
            'isMobile' => null,
            'isTouch' => null,
        ], $device->toArray());

        $device->setModel('iPad');
        $device->setBrand('Apple');
        $device->setType('tablet');
        $device->setIsMobile(false);
        $device->setIsTouch(true);

        $this->assertEquals([
            'model' => 'iPad',
            'brand' => 'Apple',
            'type' => 'tablet',
            'isMobile' => false,
            'isTouch' => true,
        ], $device->toArray());
    }
}
