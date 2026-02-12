<?php

namespace UserAgentParserTest\Integration\Provider;

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;

/**
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
class AbstractBrowscapTestCase extends AbstractProviderTestCase
{
    protected function getParserWithWarmCache(?string $type): \BrowscapPHP\Browscap
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type.'_'.$filename;
        }

        $cache = new \WurflCache\Adapter\Memory;

        $browscap = new Browscap;
        $browscap->setCache($cache);

        $updater = new BrowscapUpdater;
        $updater->setCache($cache);
        $updater->convertFile('tests/resources/browscap/'.$filename);

        return $browscap;
    }

    protected function getParserWithColdCache(?string $type): \BrowscapPHP\Browscap
    {
        $filename = 'php_browscap.ini';
        if ($type != '') {
            $filename = $type.'_'.$filename;
        }

        $cache = new \WurflCache\Adapter\Memory;

        $browscap = new Browscap;
        $browscap->setCache($cache);

        return $browscap;
    }
}
