<?php

namespace UserAgentParserTest\Unit\Provider;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 *          @covers UserAgentParser\Provider\AbstractProvider
 */
class AbstractProviderTest extends AbstractProviderTestCase
{
    public function test_get_name(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $this->assertNull($provider->getName());

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('name');
        $property->setValue($provider, 'MyName');

        $this->assertEquals('MyName', $provider->getName());
    }

    public function test_get_homepage(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $this->assertNull($provider->getHomepage());

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('homepage');
        $property->setValue($provider, 'https://github.com/vendor/package');

        $this->assertEquals('https://github.com/vendor/package', $provider->getHomepage());
    }

    public function test_get_package_name(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $this->assertNull($provider->getPackageName());

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'vendor/package');

        $this->assertEquals('vendor/package', $provider->getPackageName());
    }

    public function test_version_null(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        // no package name
        $this->assertNull($provider->getVersion());

        // no package match
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'vendor/package');

        $this->assertNull($provider->getVersion());
    }

    public function test_version(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'matomo/device-detector');

        // match
        $this->assertIsString($provider->getVersion());
    }

    public function test_update_date_null(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        // no package name
        $this->assertNull($provider->getUpdateDate());

        // no package match
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'vendor/package');

        $this->assertNull($provider->getUpdateDate());
    }

    public function test_update_date(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'matomo/device-detector');

        // match
        $this->assertInstanceOf('DateTime', $provider->getUpdateDate());
    }

    public function test_detection_capabilities(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $this->assertIsArray($provider->getDetectionCapabilities());
        $this->assertCount(5, $provider->getDetectionCapabilities());
        $this->assertFalse($provider->getDetectionCapabilities()['browser']['name']);
    }

    public function test_check_if_installed(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'thadafinser/user-agent-parser');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('checkIfInstalled');

        // no return, just no exception expected
        $method->invoke($provider);
    }

    public function test_check_if_installed_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\PackageNotLoadedException::class);

        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $property = $reflection->getProperty('packageName');
        $property->setValue($provider, 'vendor/package');

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('checkIfInstalled');

        $method->invoke($provider);
    }

    public function test_is_real_result(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('isRealResult');

        $this->assertFalse($method->invoke($provider, ''));
        $this->assertFalse($method->invoke($provider, null));

        $this->assertTrue($method->invoke($provider, 'some value'));
    }

    public function test_is_real_result_with_default_values(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('defaultValues');
        $property->setValue($provider, [
            'general' => [
                '/^default value$/i',
            ],

            'bot' => [
                'name' => [
                    '/^default other$/i',
                ],
            ],
        ]);

        $method = $reflection->getMethod('isRealResult');

        $this->assertFalse($method->invoke($provider, 'default value'));

        $this->assertTrue($method->invoke($provider, 'default other'));

        $this->assertFalse($method->invoke($provider, 'default other', 'bot', 'name'));
    }

    public function test_get_real_result(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getRealResult');

        $this->assertNull($method->invoke($provider, ''));
        $this->assertNull($method->invoke($provider, null));

        $this->assertEquals('some value', $method->invoke($provider, 'some value'));
    }

    public function test_get_real_result_with_default_values(): void
    {
        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\AbstractProvider::class);

        $reflection = new \ReflectionClass($provider);

        $property = $reflection->getProperty('defaultValues');
        $property->setValue($provider, [
            'general' => [
                '/^default value$/i',
            ],

            'bot' => [
                'name' => [
                    '/^default other$/i',
                ],
            ],
        ]);

        $method = $reflection->getMethod('getRealResult');

        $this->assertNull($method->invoke($provider, 'default value'));

        $this->assertEquals('default other', $method->invoke($provider, 'default other'));

        $this->assertNull($method->invoke($provider, 'default other', 'bot', 'name'));
    }
}
