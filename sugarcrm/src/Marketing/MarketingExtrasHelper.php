<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Marketing;

use SugarConfig;
use Sugarcrm\Sugarcrm\DependencyInjection\Container;

class MarketingExtrasHelper
{
    /**
     * Retrieve the version, flavor, build number, license,
     * and domain of this Sugar instance.
     *
     * @return array An array consisting of version, flavor, build number,
     * license, and domain.
     */
    public function getSugarDetails(): array
    {
        global $sugar_build, $sugar_flavor, $sugar_version, $license;

        return [
            'version' => $sugar_version,
            'flavor' => strtolower($sugar_flavor),
            'build' => $sugar_build,
            'license' => $license->settings['license_key'],
            'domain' => $_SERVER['HTTP_HOST'],
        ];
    }

    /**
     * Determine the language to request marketing details for.
     * @param null|string $language The client's preferred language.
     * @return string The language to use. If set, uses the client's preferred
     *   language, then falls back to the default language of this Sugar
     *   instance, and finally to en_us.
     */
    public function chooseLanguage(?string $language): string
    {
        if (isset($language)) {
            // because we have strict types, this implies it's a proper string
            return $language;
        }

        // no language given, check for system-wide default
        $defaultLanguage = $this->getSugarConfig('default_language');
        if (isset($defaultLanguage)) {
            return $defaultLanguage;
        }

        // fall back to en_us if there's no default language set
        return 'en_us';
    }

    /**
     * Get the sugar_config global variable.
     * @param string $key Key to get.
     * @param * $default Default value.
     * @return * The value of the config flag, or the default.
     */
    public function getSugarConfig(string $key, $default = null)
    {
        $container = Container::getInstance();
        $config = $container->get(SugarConfig::class);
        return $config->get($key, $default);
    }
}
