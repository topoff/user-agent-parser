<?php

namespace UserAgentParserTest\Integration\Provider\Http;

use UserAgentParser\Provider\Http\UdgerCom;

/**
 * @coversNothing
 */
class UdgerComTest extends AbstractHttpProviderTestCase
{
    public function test_invalid_credentials(): void
    {
        $this->expectException(\UserAgentParser\Exception\InvalidCredentialsException::class);
        $this->expectExceptionMessage('Your API key "invalid_api_key" is not valid for UdgerCom');

        $provider = new UdgerCom($this->getClient(), 'invalid_api_key');

        $provider->parse('...');
    }

    public function test_no_result_found(): void
    {
        $this->expectException(\UserAgentParser\Exception\NoResultFoundException::class);

        if (! defined('CREDENTIALS_UDGER_COM_KEY')) {
            $this->markTestSkipped('no credentials available. Please provide tests/credentials.php');
        }

        $this->markTestIncomplete('Currently i have no valid API key to create more integration tests');

        $provider = new UdgerCom($this->getClient(), CREDENTIALS_UDGER_COM_KEY);

        $provider->parse('...');
    }
}
