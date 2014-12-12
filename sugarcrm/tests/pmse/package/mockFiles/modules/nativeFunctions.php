<?php

function get_custom_file_if_exists($name) {
    return true;
}

function blowfishGetKey($params) {
    return 'PMKey';
}

function blowfishEncode($params) {
    return 'PMLicenseEncoded';
}

function blowfishDecode($key, $text) {
    return 'YToxOntpOjA7czoxODoiRHVtbXkgTGljZW5zZSBEYXRhIjt9';
}

function translate ($string, $module='ProcessMaker') {
    return $string;
}

function unencodeMultienum($string)
{
    if (is_array($string)) {
       return $string;
    }
    if (substr($string, 0 ,1) == "^" && substr($string, -1) == "^") {
          // Remove empty values from beginning and end of the string
          $string = preg_replace('/^(\^\^,\^)|(\^,\^\^)$/', '^', $string);

          // Get the inner part of the string without leading|trailing ^ chars
          $string = substr(substr($string, 1), 0, strlen($string) -2);
    }

    return explode('^,^', $string);
}

function get_rel_module_name($module)
{
    return new stdClass();
}

function get_module_info($param = null)
{
    return $param;
}

function from_html($html)
{
    return $html;
}

function create_guid()
{
    return 'someGUID';
}

function return_module_language($param = array(), $type = '')
{
    return array(
        'fieldTypes' => array(
            'datetimecombo' => 'someField'
        )
    );
}