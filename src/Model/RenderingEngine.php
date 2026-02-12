<?php

namespace UserAgentParser\Model;

/**
 * Rendering engine model
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class RenderingEngine
{
    /**
     * @var string
     */
    private $name;

    private Version $version;

    public function __construct()
    {
        $this->version = new Version;
    }

    /**
     * @param  string  $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setVersion(Version $version): void
    {
        $this->version = $version;
    }

    public function getVersion(): Version
    {
        return $this->version;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'version' => $this->getVersion()->toArray(),
        ];
    }
}
