<?php

namespace UserAgentParser\Model;

/**
 * Version model
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Version
{
    private ?int $major = null;

    private ?int $minor = null;

    private ?int $patch = null;

    /**
     * @var string
     */
    private $alias;

    /**
     * @var string
     */
    private $complete;

    private static array $notAllowedAlias = [
        'a',
        'alpha',
        'prealpha',

        'b',
        'beta',
        'prebeta',

        'rc',
    ];

    /**
     * @param  int  $major
     */
    public function setMajor($major): void
    {
        if ($major !== null) {
            $major = (int) $major;
        }

        $this->major = $major;

        $this->hydrateComplete();
    }

    public function getMajor(): ?int
    {
        return $this->major;
    }

    /**
     * @param  int  $minor
     */
    public function setMinor($minor): void
    {
        if ($minor !== null) {
            $minor = (int) $minor;
        }

        $this->minor = $minor;

        $this->hydrateComplete();
    }

    public function getMinor(): ?int
    {
        return $this->minor;
    }

    /**
     * @param  int  $patch
     */
    public function setPatch($patch): void
    {
        if ($patch !== null) {
            $patch = (int) $patch;
        }

        $this->patch = $patch;

        $this->hydrateComplete();
    }

    public function getPatch(): ?int
    {
        return $this->patch;
    }

    /**
     * @param  string  $alias
     */
    public function setAlias($alias): void
    {
        $this->alias = $alias;

        $this->hydrateComplete();
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Set from the complete version string.
     *
     * @param  string  $complete
     */
    public function setComplete($complete): void
    {
        // check if the version has only 0 -> so no real result
        // maybe move this out to the Providers itself?
        $left = preg_replace('/[0._]/', '', $complete);
        if ($left === '') {
            $complete = null;
        }

        $this->hydrateFromComplete($complete);

        $this->complete = $complete;
    }

    /**
     * @return string
     */
    public function getComplete()
    {
        return $this->complete;
    }

    public function toArray(): array
    {
        return [
            'major' => $this->getMajor(),
            'minor' => $this->getMinor(),
            'patch' => $this->getPatch(),

            'alias' => $this->getAlias(),

            'complete' => $this->getComplete(),
        ];
    }

    private function hydrateComplete(): void
    {
        if ($this->getMajor() === null && $this->getAlias() === null) {
            return;
        }

        $version = $this->getMajor();

        if ($this->getMinor() !== null) {
            $version .= '.'.$this->getMinor();
        }

        if ($this->getPatch() !== null) {
            $version .= '.'.$this->getPatch();
        }

        if ($this->getAlias() !== null) {
            $version = $this->getAlias().' - '.$version;
        }

        $this->complete = $version;
    }

    private function hydrateFromComplete($complete): void
    {
        $parts = $this->getCompleteParts($complete);

        $this->setMajor($parts['major']);
        $this->setMinor($parts['minor']);
        $this->setPatch($parts['patch']);
        $this->setAlias($parts['alias']);
    }

    private function getCompleteParts($complete): array
    {
        $versionParts = [
            'major' => null,
            'minor' => null,
            'patch' => null,

            'alias' => null,
        ];

        // only digits
        preg_match("/\d+(?:[._]*\d*)*/", (string) $complete, $result);
        if (count($result) > 0) {
            $parts = preg_split('/[._]/', $result[0]);

            if (isset($parts[0]) && $parts[0] != '') {
                $versionParts['major'] = (int) $parts[0];
            }
            if (isset($parts[1]) && $parts[1] != '') {
                $versionParts['minor'] = (int) $parts[1];
            }
            if (isset($parts[2]) && $parts[2] != '') {
                $versionParts['patch'] = (int) $parts[2];
            }
        }

        // grab alias
        $result = preg_split("/\d+(?:[._]*\d*)*/", (string) $complete);
        foreach ($result as $row) {
            $row = trim($row);

            if ($row === '') {
                continue;
            }

            // do not use beta and other things
            if (in_array($row, self::$notAllowedAlias)) {
                continue;
            }

            $versionParts['alias'] = $row;
        }

        return $versionParts;
    }
}
