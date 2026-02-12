<?php

namespace UserAgentParser\Model;

/**
 * User agent model
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class UserAgent
{
    private Browser $browser;

    private RenderingEngine $renderingEngine;

    private OperatingSystem $operatingSystem;

    private Device $device;

    private Bot $bot;

    /**
     * @var mixed
     */
    private $providerResultRaw;

    /**
     * @param  string  $provider
     * @param  string  $providerName
     * @param  string  $providerVersion
     */
    public function __construct(/**
     * Provider name
     */
        private $providerName = null, /**
     * Provider version
     */
        private $providerVersion = null)
    {
        $this->browser = new Browser;
        $this->renderingEngine = new RenderingEngine;
        $this->operatingSystem = new OperatingSystem;
        $this->device = new Device;
        $this->bot = new Bot;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @return string
     */
    public function getProviderVersion()
    {
        return $this->providerVersion;
    }

    public function setBrowser(Browser $browser): void
    {
        $this->browser = $browser;
    }

    public function getBrowser(): Browser
    {
        return $this->browser;
    }

    public function setRenderingEngine(RenderingEngine $renderingEngine): void
    {
        $this->renderingEngine = $renderingEngine;
    }

    public function getRenderingEngine(): RenderingEngine
    {
        return $this->renderingEngine;
    }

    public function setOperatingSystem(OperatingSystem $operatingSystem): void
    {
        $this->operatingSystem = $operatingSystem;
    }

    public function getOperatingSystem(): OperatingSystem
    {
        return $this->operatingSystem;
    }

    public function setDevice(Device $device): void
    {
        $this->device = $device;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }

    public function setBot(Bot $bot): void
    {
        $this->bot = $bot;
    }

    public function getBot(): Bot
    {
        return $this->bot;
    }

    public function isBot(): bool
    {
        return $this->getBot()->getIsBot() === true;
    }

    public function isMobile(): bool
    {
        return $this->getDevice()->getIsMobile() === true;
    }

    /**
     * @param  mixed  $providerResultRaw
     */
    public function setProviderResultRaw($providerResultRaw): void
    {
        $this->providerResultRaw = $providerResultRaw;
    }

    /**
     * @return mixed
     */
    public function getProviderResultRaw()
    {
        return $this->providerResultRaw;
    }

    public function toArray($includeResultRaw = false): array
    {
        $data = [
            'browser' => $this->getBrowser()->toArray(),
            'renderingEngine' => $this->getRenderingEngine()->toArray(),
            'operatingSystem' => $this->getOperatingSystem()->toArray(),
            'device' => $this->getDevice()->toArray(),
            'bot' => $this->getBot()->toArray(),
        ];

        // should be only used for debug
        if ($includeResultRaw === true) {
            $data['providerResultRaw'] = $this->getProviderResultRaw();
        }

        return $data;
    }
}
