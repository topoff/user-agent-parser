<?php

namespace UserAgentParserTest\Unit\Provider;

/**
 * A interface with required test methods for each provider
 *
 * @author Martin Keckeis <martin.keckeis1@gmail.com>
 * @license MIT
 */
interface RequiredProviderTestInterface
{
    public function test_get_name();

    public function test_get_homepage();

    public function test_get_package_name();

    public function test_version();

    public function test_update_date();

    public function test_detection_capabilities();

    public function test_parse_no_result_found_exception();

    public function test_is_real_result();

    public function test_provider_name_and_version_is_in_result();
}
