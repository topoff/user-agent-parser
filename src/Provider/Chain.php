<?php

namespace UserAgentParser\Provider;

use UserAgentParser\Exception;

/**
 * A chain provider, to use multiple providers at the same time
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class Chain extends AbstractProvider
{
    /**
     * Name of the provider
     *
     * @var string
     */
    protected $name = 'Chain';

    /**
     * @param  AbstractProvider[]  $providers
     */
    public function __construct(private readonly array $providers = []) {}

    /**
     * @return AbstractProvider[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    public function parse($userAgent, array $headers = [])
    {
        foreach ($this->getProviders() as $provider) {
            /* @var $provider \UserAgentParser\Provider\AbstractProvider */

            try {
                return $provider->parse($userAgent, $headers);
            } catch (Exception\NoResultFoundException) {
                // just catch this and continue to the next provider
            }
        }

        throw new Exception\NoResultFoundException('No result found for user agent: '.$userAgent);
    }
}
