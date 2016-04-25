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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Agent;

class Client
{
    /**
     * @var array Patterns for parse name and version client.
     */
    protected $patterns = array(
        '#(mac[\s_+]os[\s_+]x)/([\d.]+).+(calendaragent)/([\d.]+)#i' =>
            array('Mac OS X', 2, 'NativeCalendarApplication', 4),
        '#(ios)/([\d.]+).+(accountsd)/([\d.]+)#i' => array('iOS', 2, 'NativeCalendarApplication', 4),
    );

    /**
     * Set patters for parse user-agent.
     *
     * @param array $patterns
     * @return $this
     */
    public function setParsePatterns(array $patterns = array())
    {
        $this->patterns = array();
        foreach ($patterns as $pattern => $definition) {
            if (is_array($definition) && array_keys($definition) === range(0, 3)) {
                $this->patterns[$pattern] = $definition;
            }
        }

        return $this;
    }

    /**
     * Parse user-agent.
     *
     * @param $userAgent
     * @return array|null
     */
    public function parse($userAgent)
    {
        $result = null;

        foreach ($this->patterns as $pattern => $definition) {
            if (preg_match($pattern, $userAgent, $matches)) {
                $result = array(
                    'platformName' => '',
                    'platformVersion' => '',
                    'clientName' => '',
                    'clientVersion' => '',
                );

                if (is_int($definition[0]) && isset($matches[$definition[0]])) {
                    $result['platformName'] = $matches[$definition[0]];
                } elseif (is_string($definition[0])) {
                    $result['platformName'] = $definition[0];
                }

                if (is_int($definition[1]) && isset($matches[$definition[1]])) {
                    $result['platformVersion'] = $matches[$definition[1]];
                } elseif (is_string($definition[1])) {
                    $result['platformVersion'] = $definition[1];
                }

                if (is_int($definition[2]) && isset($matches[$definition[2]])) {
                    $result['clientName'] = $matches[$definition[2]];
                } elseif (is_string($definition[2])) {
                    $result['clientName'] = $definition[2];
                }

                if (is_int($definition[3]) && isset($matches[$definition[3]])) {
                    $result['clientVersion'] = $matches[$definition[3]];
                } elseif (is_string($definition[3])) {
                    $result['clientVersion'] = $definition[3];
                }
            }
        }

        return $result;
    }
}
