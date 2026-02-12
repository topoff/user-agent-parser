<?php

namespace UserAgentParser\Model;

/**
 * Bot model
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Bot
{
    /**
     * @var bool
     */
    private $isBot;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @param  bool  $mode
     */
    public function setIsBot($mode): void
    {
        $this->isBot = $mode;
    }

    /**
     * @return bool
     */
    public function getIsBot()
    {
        return $this->isBot;
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

    /**
     * @param  string  $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'isBot' => $this->getIsBot(),
            'name' => $this->getName(),
            'type' => $this->getType(),
        ];
    }
}
