<?php

namespace UserAgentParserTest\Integration\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use UserAgentParserTest\Integration\Provider\AbstractProviderTestCase;

abstract class AbstractHttpProviderTestCase extends AbstractProviderTestCase
{
    private ?\GuzzleHttp\Client $client = null;

    protected function setUp(): void
    {
        /*
         * move tests/credentials.php.dist to tests/credentials.php
         */
        if (! defined('CREDENTIALS_FILE_LOADED') && file_exists('tests/credentials.php')) {
            include __DIR__.'/tests/credentials.php';
        }

        /*
         * If you need an alternativ client to test the integration -> move test/client.php.dist to test/client.php and define your things!
         */
        if (file_exists('tests/client.php')) {
            $client = include __DIR__.'/tests/client.php';

            if ($client instanceof Client) {
                $this->client = $client;
            }
        }
    }

    /**
     * @return Client
     */
    protected function getClient()
    {
        if (! $this->client instanceof \GuzzleHttp\Client) {
            $handler = new CurlHandler;
            $stack = HandlerStack::create($handler);

            $this->client = new Client([
                'handler' => $stack,
                'timeout' => 5,

                'curl' => [
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                ],
            ]);
        }

        return $this->client;
    }
}
