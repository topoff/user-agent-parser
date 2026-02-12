<?php

namespace UserAgentParserTest\Unit\Provider\Http;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use UserAgentParserTest\Unit\Provider\AbstractProviderTestCase;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @covers UserAgentParser\Provider\Http\AbstractHttpProvider
 */
class AbstractHttpProviderTest extends AbstractProviderTestCase
{
    /**
     * A general RequestException
     */
    public function test_get_result_request_exception(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $responseQueue = [
            new RequestException('Error Communicating with Server', new Request('GET', 'test')),
        ];

        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\Http\AbstractHttpProvider::class, [
            $this->getClient($responseQueue),
        ]);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getResponse');

        $request = new Request('GET', 'http://example.com');

        $method->invoke($provider, $request);
    }

    /**
     * Got a response, but not 200
     */
    public function test_get_result_request_exception_not_status200(): void
    {
        $this->expectException(\UserAgentParser\Exception\RequestException::class);

        $responseQueue = [
            new Response(202),
        ];

        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\Http\AbstractHttpProvider::class, [
            $this->getClient($responseQueue),
        ]);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getResponse');

        $request = new Request('GET', 'http://example.com');

        $method->invoke($provider, $request);
    }

    /**
     * Valid response
     */
    public function test_get_result_valid(): void
    {
        $responseQueue = [
            new Response(200),
        ];

        $provider = $this->getMockForAbstractClass(\UserAgentParser\Provider\Http\AbstractHttpProvider::class, [
            $this->getClient($responseQueue),
        ]);

        $reflection = new \ReflectionClass($provider);
        $method = $reflection->getMethod('getResponse');

        $request = new Request('GET', 'http://example.com');

        $result = $method->invoke($provider, $request);

        $this->assertInstanceOf(\GuzzleHttp\Psr7\Response::class, $result);
    }
}
