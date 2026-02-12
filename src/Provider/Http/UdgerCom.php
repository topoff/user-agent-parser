<?php

namespace UserAgentParser\Provider\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use stdClass;
use UserAgentParser\Exception;
use UserAgentParser\Model;

/**
 * Abstraction of udger.com
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 *
 * @see https://udger.com/support/documentation/?doc=38
 */
class UdgerCom extends AbstractHttpProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'UdgerCom';

    /**
     * Homepage of the provider
     *
     * @var string
     */
    protected $homepage = 'https://udger.com/';

    protected $detectionCapabilities = [

        'browser' => [
            'name' => true,
            'version' => true,
        ],

        'renderingEngine' => [
            'name' => true,
            'version' => false,
        ],

        'operatingSystem' => [
            'name' => true,
            'version' => false,
        ],

        'device' => [
            'model' => false,
            'brand' => false,
            'type' => true,
            'isMobile' => false,
            'isTouch' => false,
        ],

        'bot' => [
            'isBot' => true,
            'name' => false,
            'type' => false,
        ],
    ];

    protected $defaultValues = [
        'general' => [
            '/^unknown$/i',
        ],
    ];

    private static string $uri = 'http://api.udger.com/parse';

    public function __construct(Client $client, private $apiKey)
    {
        parent::__construct($client);
    }

    #[\Override]
    public function getVersion() {}

    /**
     * @return stdClass
     *
     * @throws Exception\RequestException
     */
    protected function getResult(?string $userAgent, array $headers)
    {
        /*
         * an empty UserAgent makes no sense
         */
        if ($userAgent == '') {
            throw new Exception\NoResultFoundException('No result found for user agent: '.$userAgent);
        }

        $params = [
            'accesskey' => $this->apiKey,
            'uastrig' => $userAgent,
        ];

        $body = http_build_query($params, null, '&');

        $request = new Request('POST', self::$uri, [
            'Content-Type' => 'application/x-www-form-urlencoded',
        ], $body);

        $response = $this->getResponse($request);

        /*
         * no json returned?
         */
        $contentType = $response->getHeader('Content-Type');
        if (! isset($contentType[0]) || $contentType[0] != 'application/json') {
            throw new Exception\RequestException('Could not get valid "application/json" response from "'.$request->getUri().'". Response is "'.$response->getBody()->getContents().'"');
        }

        $content = json_decode($response->getBody()->getContents());

        /*
         * No result found?
         */
        if (isset($content->flag) && $content->flag == 3) {
            throw new Exception\NoResultFoundException('No result found for user agent: '.$userAgent);
        }

        /*
         * Errors
         */
        if (isset($content->flag) && $content->flag == 4) {
            throw new Exception\InvalidCredentialsException('Your API key "'.$this->apiKey.'" is not valid for '.$this->getName());
        }

        if (isset($content->flag) && $content->flag == 6) {
            throw new Exception\LimitationExceededException('Exceeded the maximum number of request with API key "'.$this->apiKey.'" for '.$this->getName());
        }

        if (isset($content->flag) && $content->flag > 3) {
            throw new Exception\RequestException('Could not get valid response from "'.$request->getUri().'". Response is "'.$response->getBody()->getContents().'"');
        }

        /*
         * Missing data?
         */
        if (! $content instanceof stdClass || ! isset($content->info)) {
            throw new Exception\RequestException('Could not get valid response from "'.$request->getUri().'". Response is "'.$response->getBody()->getContents().'"');
        }

        return $content;
    }

    private function isBot(stdClass $resultRaw): bool
    {
        return isset($resultRaw->type) && $resultRaw->type === 'Robot';
    }

    private function hydrateBot(Model\Bot $bot, stdClass $resultRaw): void
    {
        $bot->setIsBot(true);

        if (isset($resultRaw->ua_family)) {
            $bot->setName($this->getRealResult($resultRaw->ua_family));
        }
    }

    private function hydrateBrowser(Model\Browser $browser, stdClass $resultRaw): void
    {
        if (isset($resultRaw->ua_family)) {
            $browser->setName($this->getRealResult($resultRaw->ua_family, 'browser', 'name'));
        }

        if (isset($resultRaw->ua_ver)) {
            $browser->getVersion()->setComplete($this->getRealResult($resultRaw->ua_ver));
        }
    }

    private function hydrateRenderingEngine(Model\RenderingEngine $engine, stdClass $resultRaw): void
    {
        if (isset($resultRaw->ua_engine)) {
            $engine->setName($this->getRealResult($resultRaw->ua_engine));
        }
    }

    private function hydrateOperatingSystem(Model\OperatingSystem $os, stdClass $resultRaw): void
    {
        if (isset($resultRaw->os_family)) {
            $os->setName($this->getRealResult($resultRaw->os_family));
        }
    }

    private function hydrateDevice(Model\Device $device, stdClass $resultRaw): void
    {
        if (isset($resultRaw->device_name)) {
            $device->setType($this->getRealResult($resultRaw->device_name));
        }
    }

    public function parse($userAgent, array $headers = []): \UserAgentParser\Model\UserAgent
    {
        $resultRaw = $this->getResult($userAgent, $headers);

        /*
         * Hydrate the model
         */
        $result = new Model\UserAgent($this->getName(), $this->getVersion());
        $result->setProviderResultRaw($resultRaw);

        /*
         * Bot detection
         */
        if ($this->isBot($resultRaw->info)) {
            $this->hydrateBot($result->getBot(), $resultRaw->info);

            return $result;
        }

        /*
         * hydrate the result
         */
        $this->hydrateBrowser($result->getBrowser(), $resultRaw->info);
        $this->hydrateRenderingEngine($result->getRenderingEngine(), $resultRaw->info);
        $this->hydrateOperatingSystem($result->getOperatingSystem(), $resultRaw->info);
        $this->hydrateDevice($result->getDevice(), $resultRaw->info);

        return $result;
    }
}
