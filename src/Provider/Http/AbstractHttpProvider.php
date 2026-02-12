<?php

namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException as GuzzleHttpException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use UserAgentParser\Exception;
use UserAgentParser\Provider\AbstractProvider;

/**
 * Abstraction for all HTTP providers
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
abstract class AbstractHttpProvider extends AbstractProvider
{
    public function __construct(private readonly Client $client) {}

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Response
     *
     * @throws Exception\RequestException
     */
    protected function getResponse(RequestInterface $request)
    {
        try {
            /* @var $response \GuzzleHttp\Psr7\Response */
            $response = $this->getClient()->send($request);
        } catch (GuzzleHttpException $ex) {
            throw new Exception\RequestException('Could not get valid response from "'.$request->getUri().'"', null, $ex);
        }

        if ($response->getStatusCode() !== 200) {
            throw new Exception\RequestException('Could not get valid response from "'.$request->getUri().'". Status code is: "'.$response->getStatusCode().'"');
        }

        return $response;
    }
}
