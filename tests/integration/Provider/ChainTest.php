<?php

namespace UserAgentParserTest\Integration\Provider;

use UserAgentParser\Provider\Chain;
use UserAgentParser\Provider\MatomoDeviceDetector;
use UserAgentParser\Provider\WhichBrowser;
use UserAgentParser\Provider\Zsxsoft;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @coversNothing
 */
class ChainTest extends AbstractProviderTestCase
{
    public function test_no_result_found_single_provider(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new Chain([
            new WhichBrowser,
        ]);

        $provider->parse('...');
    }

    /**
     * Also with multiple providers the excepction must be thrown
     */
    public function test_no_result_found_multiple_providers(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        $provider = new Chain([
            new WhichBrowser,
            new Zsxsoft,
            new MatomoDeviceDetector,
        ]);

        $provider->parse('...');
    }

    public function test_real_result_single_provider(): void
    {
        $provider = new Chain([
            new WhichBrowser,
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        $this->assertTrue($result->getBot()
            ->getIsBot());
    }

    /**
     * This test makes sure, that the chain provider go to the next provider when no result is found
     */
    public function test_real_result_two_provider_second_used(): void
    {
        $provider = new Chain([
            new Zsxsoft,
            new MatomoDeviceDetector,
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        // Zsxsoft cannot detect bots, so true here is not possible
        $this->assertTrue($result->getBot()
            ->getIsBot());
    }

    /**
     * This test makes sure, that the chain provider stops when a result is found
     */
    public function test_real_result_three_provider_second_used(): void
    {
        $provider = new Chain([
            new Zsxsoft,
            new MatomoDeviceDetector,
            new WhichBrowser,
        ]);

        $result = $provider->parse('Googlebot/2.1 (+http://www.google.com/bot.html)');

        // Zsxsoft cannot detect bots!
        $this->assertTrue($result->getBot()
            ->getIsBot());
        // WhichBrowser cannot detect the bot type
        $this->assertEquals('Search bot', $result->getBot()
            ->getType());
    }
}
