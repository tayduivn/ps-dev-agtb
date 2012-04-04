<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 * $Id: additionalDetails.php 13782 2006-06-06 17:58:55Z majed $
 *********************************************************************************/
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty plugin:
 * This is a Smarty plugin to create a multi-level menu using nasted ul lists.
 * The generated structure looks like this.
 * <ul $htmlOptions>
 *      <li $itemOptions>
 *          <elem></elem>
 *          <ul $submenuHtmlOptions>
 *              <li $itemOptions></li>
 *              <li $itemOptions>
 *                  <elem></elem>
 *                  <ul $submenuHtmlOptions>
 *                      <li $itemOptions></li>
 *                      ...
 *                  </ul>
 *              </li>
 *              ...
 *          </ul>
 *      </li>
 *      ...
 *  </ul>
 *
 *
 * @param $params array - look up the bellow example
 * @param $smarty
 * @return string - generated HTML code
 *
 * <pre>
 * smarty_function_sugar_menu(array(
 *      'id' => $string, //id property that is applied in root UL
 *      'items' => array(
 *          array(
 *              'html' => $html_string, //html container that renders in the LI tag
 *              'items' => array(), //nasted ul lists
 *          )
 *      ),
 *      'htmlOptions' => attributes that is applied in root UL, such as class, or align.
 *      'itemOptions' => attributes that is applied in LI items, such as class, or align.
 *      'submenuHtmlOptions' => attributes that is applied in child UL, such as class, or align.
 * ), $smarty);
 *
 * </pre>
 * * @author Justin Park (jpark@sugarcrm.com)
 */
function smarty_function_sugar_menu($params, &$smarty)
{
    $root_options = array(
        "id" => array_key_exists('id', $params) ? $params['id'] : ""
    );
    if(array_key_exists('htmlOptions', $params)) {
        foreach($params['htmlOptions'] as $attr => $value) {
            $root_options[$attr] = $value;
        }
    }
    $output = open_tag("ul", $root_options);
    foreach($params['items'] as $item) {
        if(strpos($item['html'], "</") === 0) {
            $output .= $item['html'];
            continue;
        }
        $output .= open_tag('li', !empty($params['itemOptions']) ? $params['itemOptions'] : array()).$item['html'];
        if(isset($item['items']) && count($item['items'])) {
            $output .= smarty_function_sugar_menu(array(
                'items' => $item['items'],
                'htmlOptions' => !empty($params['submenuHtmlOptions']) ? $params['submenuHtmlOptions'] : array()
            ), $smarty);
        }
        $output .= "</li>";
    }
    $output .= '</ul>';
    return $output;
}

function open_tag($tagName, $params = array(), $self_closing = false) {

    $options = "";
    $self_closing_tag = ($self_closing) ? "/" : "";
    if(empty($params))
        return "<{$tagName}{$self_closing_tag}>";

    foreach($params as $attr => $value) {
        if($value)
            $options .= $attr.'="'.$value.'" ';
    }
    return "<{$tagName} {$options}{$self_closing_tag}>";
}

function parse_html_tag($code, $appendTo = array()) {
    $SINGLE_QUOTE = "'";
    $DOUBLE_QUOTE = '"';
    $ASSIGN_SIGN = "=";
    $TAG_BEGIN = "<";
    $TAG_END = ">";
    $SMARTY_BEGIN = "{";
    $SMARTY_END = "}";
    $quote_encoded = false;
    $smarty_encoded = false;
    $cache = array();
    $var_name = '';
    $var_assign = false;
    $start_pos = strpos($code, ' ') ? strpos($code, ' ') : strpos($code, $TAG_END);
    if(substr($code, 0, 1) != $TAG_BEGIN || $start_pos === false) {
        return $code;
    }
    $tag = substr($code, 1, $start_pos - 1);
    $closing_tag = '</'.$tag;
    $end_pos = strpos($code, $closing_tag, $start_pos + 1);
    $output = array(
        'tag' => $tag
    );
    if($end_pos === false) {
        $output['self_closing'] = true;
        $end_pos = (substr($code, -2) == '/>') ? -2 : -1;
        $code = substr($code, $start_pos + 1, $end_pos);
    } else {
        $output['self_closing'] = false;
        $code = substr($code, $start_pos + 1, $end_pos - $start_pos - 1);
    }
    for($i = 0; $i < strlen($code) ; $i ++) {
        $char = $code[$i];
        if($char == $SINGLE_QUOTE || $char == $DOUBLE_QUOTE) {
            if(empty($quote_type)) {
                $quote_encoded = true;
                $quote_type = $char;
            } else if ($quote_type == $char) {
                if(!empty($cache)) {
                    $string = implode('', $cache);
                    if(empty($var_name)) {
                        $var_name = $string;
                    } else if($var_assign) {
                        $output[$var_name] = $string;
                        unset($var_name);
                    }
                }
                $quote_type = '';
                $var_assign = false;
                $cache = array();
                $quote_encoded = false;
            } else {
                array_push($cache, $char);
            }
        } else if ( !$quote_encoded && $char == ' ' ) {
            if(!empty($cache)) {
                $string = implode('', $cache);
                if(empty($var_name)) {
                    $var_name = $string;
                } else if($var_assign) {
                    $output[$var_name] = $string;
                    unset($var_name);
                }
                $quote_encoded = false;
                $var_assign = false;
                $cache = array();
            }
        } else if ( !$quote_encoded && $char == $ASSIGN_SIGN ) {
            if(!empty($var_name)) {
                $output[$var_name] = '';
            }
            $string = implode('', $cache);
            $var_name = $string;
            $var_assign = true;
            $cache = array();
        } else if ( !$quote_encoded && $char == $SMARTY_BEGIN) {
            $_str = ltrim(substr($code, $i + 1));
            $_left = strpos($_str, ' ');

            strpos($code, ' ',$i);

        } else if ( !$quote_encoded && $char == $TAG_END ) {
            break;
        } else {
            array_push($cache, $char);
        }
    }
    if($output['self_closing'] === false) {
        $output['container'] = substr($code, $i + 1);
    }
    return (empty($appendTo)) ? $output : array_merge($output, $appendTo);
}
