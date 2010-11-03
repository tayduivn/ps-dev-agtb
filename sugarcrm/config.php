<?php
// created: 2010-10-06 18:11:01
$sugar_config = array (
  'admin_access_control' => false,
  'admin_export_only' => false,
  'cache_dir' => 'cache/',
  'calculate_response_time' => true,
  'common_ml_dir' => '',
  'create_default_user' => false,
  'currency' => '',
  'dashlet_display_row_options' => 
  array (
    0 => '1',
    1 => '3',
    2 => '5',
    3 => '10',
  ),
  'date_formats' => 
  array (
    'Y-m-d' => '2006-12-23',
    'm-d-Y' => '12-23-2006',
    'd-m-Y' => '23-12-2006',
    'Y/m/d' => '2006/12/23',
    'm/d/Y' => '12/23/2006',
    'd/m/Y' => '23/12/2006',
    'Y.m.d' => '2006.12.23',
    'd.m.Y' => '23.12.2006',
    'm.d.Y' => '12.23.2006',
  ),
  'datef' => 'Y-m-d',
  'db' => 
  array (
    'slave_select' => 
    array (
      'db_host_name' => 'si-db2',
      'db_user_name' => 'slave_select',
      'db_password' => 'iv1aikewi(',
      'db_name' => 'sugarinternal',
      'db_type' => 'mysql',
    ),
    'write_to_this_db' => 
    array (
      'db_host_name' => 'si-db1',
      'db_user_name' => 'sugarinternal',
      'db_password' => 'rI3pSTukiD6D',
      'db_name' => 'sugarinternal',
      'db_type' => 'mysql',
    ),
    'listviews' => 
    array (
      'db_host_name' => 'si-db2',
      'db_user_name' => 'slave_select',
      'db_password' => 'iv1aikewi(',
      'db_name' => 'sugarinternal',
      'db_type' => 'mysql',
    ),
  ),
  'dbconfig' => 
  array (
    'db_host_name' => 'si-db1',
    'db_user_name' => 'sugarinternal',
    'db_password' => 'rI3pSTukiD6D',
    'db_name' => 'sugarinternal',
    'db_type' => 'mysql',
  ),
  'dbconfigoption' => 
  array (
    'persistent' => true,
    'autofree' => false,
    'debug' => 0,
    'seqname_format' => '%s_seq',
    'portability' => 0,
    'ssl' => false,
  ),
  'default_action' => 'index',
  'default_charset' => 'ISO-8859-1',
  'default_currencies' => 
  array (
    'AUD' => 
    array (
      'name' => 'Austrailian Dollars',
      'iso4217' => 'AUD',
      'symbol' => '$',
    ),
    'BRL' => 
    array (
      'name' => 'Brazilian Reais',
      'iso4217' => 'BRL',
      'symbol' => 'R$',
    ),
    'GBP' => 
    array (
      'name' => 'British Pounds',
      'iso4217' => 'GBP',
      'symbol' => '£',
    ),
    'CAD' => 
    array (
      'name' => 'Candian Dollars',
      'iso4217' => 'CAD',
      'symbol' => '$',
    ),
    'CNY' => 
    array (
      'name' => 'Chinese Yuan',
      'iso4217' => 'CNY',
      'symbol' => '元',
    ),
    'EUR' => 
    array (
      'name' => 'Euro',
      'iso4217' => 'EUR',
      'symbol' => '€',
    ),
    'HKD' => 
    array (
      'name' => 'Hong Kong Dollars',
      'iso4217' => 'HKD',
      'symbol' => '$',
    ),
    'INR' => 
    array (
      'name' => 'Indian Rupees',
      'iso4217' => 'INR',
      'symbol' => '₨',
    ),
    'KRW' => 
    array (
      'name' => 'Korean Won',
      'iso4217' => 'KRW',
      'symbol' => '₩',
    ),
    'YEN' => 
    array (
      'name' => 'Japanese Yen',
      'iso4217' => 'JPY',
      'symbol' => '¥',
    ),
    'MXM' => 
    array (
      'name' => 'Mexican Pesos',
      'iso4217' => 'MXM',
      'symbol' => '$',
    ),
    'SGD' => 
    array (
      'name' => 'Singaporean Dollars',
      'iso4217' => 'SGD',
      'symbol' => '$',
    ),
    'CHF' => 
    array (
      'name' => 'Swiss Franc',
      'iso4217' => 'CHF',
      'symbol' => 'SFr.',
    ),
    'THB' => 
    array (
      'name' => 'Thai Baht',
      'iso4217' => 'THB',
      'symbol' => '฿',
    ),
    'USD' => 
    array (
      'name' => 'US Dollars',
      'iso4217' => 'USD',
      'symbol' => '$',
    ),
  ),
  'default_currency_iso4217' => 'USD',
  'default_currency_name' => 'US Dollar',
  'default_currency_significant_digits' => 2,
  'default_currency_symbol' => '$',
  'default_date_format' => 'm-d-Y',
  'default_decimal_seperator' => '.',
  'default_email_charset' => 'UTF-8',
  'default_export_charset' => 'CP1252',
  'default_language' => 'en_us',
  'default_locale_name_format' => 's f l',
  'default_max_subtabs' => '10',
  'default_max_tabs' => '7',
  'default_module' => 'Home',
  'default_module_favicon' => false,
  'default_navigation_paradigm' => 'm',
  'default_number_grouping_seperator' => ',',
  'default_password' => '',
  'default_permissions' => 
  array (
    'dir_mode' => 1528,
    'file_mode' => 432,
    'user' => '',
    'group' => '',
  ),
  'default_subpanel_links' => false,
  'default_subpanel_tabs' => true,
  'default_swap_last_viewed' => false,
  'default_swap_shortcuts' => false,
  'default_theme' => 'Sugar',
  'default_time_format' => 'h:ia',
  'default_user_is_admin' => false,
  'default_user_name' => '',
  'developerMode' => false,
  'disable_count_query' => true,
  'disable_export' => false,
  'disable_persistent_connections' => 'false',
  'disc_client' => false,
  'display_email_template_variable_chooser' => false,
  'display_inbound_email_buttons' => false,
  'dump_slow_queries' => true,
  'email_default_client' => 'sugar',
  'email_default_delete_attachments' => true,
  'email_default_editor' => 'html',
  'email_inbound_save_raw' => false,
  'email_num_autoreplies_24_hours' => '10',
  'email_outbound_save_raw' => false,
  'email_xss' => 'YToxMjp7czo2OiJhcHBsZXQiO3M6NjoiYXBwbGV0IjtzOjQ6ImJhc2UiO3M6NDoiYmFzZSI7czo1OiJlbWJlZCI7czo1OiJlbWJlZCI7czo0OiJmb3JtIjtzOjQ6ImZvcm0iO3M6NToiZnJhbWUiO3M6NToiZnJhbWUiO3M6ODoiZnJhbWVzZXQiO3M6ODoiZnJhbWVzZXQiO3M6NjoiaWZyYW1lIjtzOjY6ImlmcmFtZSI7czo2OiJpbXBvcnQiO3M6ODoiXD9pbXBvcnQiO3M6NToibGF5ZXIiO3M6NToibGF5ZXIiO3M6NDoibGluayI7czo0OiJsaW5rIjtzOjY6Im9iamVjdCI7czo2OiJvYmplY3QiO3M6MzoieG1wIjtzOjM6InhtcCI7fQ==',
  'export_delimiter' => ',',
  'external_cache_disabled_memcache' => true,
  'external_cache_disabled_zend' => true,
  'history_max_viewed' => '50',
  'host_name' => 'localhost',
  'http_referer' => 
  array (
    'list' => 
    array (
      0 => 'internalwiki.sjc.sugarcrm.pvt',
      1 => 'mex07a.emailsrvr.com',
    ),
  ),
  'import_dir' => 'cache/import/',
  'import_max_execution_time' => 3600,
  'import_max_records_per_file' => '50',
  'inbound_email_case_subject_macro' => '[CASE:%1]',
  'installer_locked' => true,
  'js_custom_version' => '1',
  'js_lang_version' => 11,
  'languages' => 
  array (
    'en_us' => 'US English',
    'ja' => 'Japanese - 日本語',
    'fr_fr' => 'French - Français',
    'zh_cn' => 'Chinese - 简体中文',
  ),
  'large_scale_test' => false,
  'list_max_entries_per_page' => '20',
  'list_max_entries_per_subpanel' => '5',
  'lock_default_user_name' => false,
  'lock_homepage' => false,
  'lock_subpanels' => false,
  'log_dir' => '/var/www/sugarinternal/logs',
  'log_file' => 'sugarcrm.log',
  'log_memory_usage' => false,
  'logger' => 
  array (
    'file' => 
    array (
      'maxSize' => '100MB',
      'dateFormat' => '%c',
      'ext' => '.log',
      'name' => 'sugarcrm',
      'maxLogs' => 10,
      'suffix' => '%m_%Y',
    ),
    'level' => 'info',
  ),
  'login_nav' => false,
  'max_dashlets_homepage' => '15',
  'new_subpanels' => true,
  'oc_converted' => false,
  'oc_password' => 'ae2b1fca515949e5d54fb22b8ed95575',
  'oc_username' => 'fatman',
  'passwordsetting' => 
  array (
    'minpwdlength' => '6',
    'maxpwdlength' => '',
    'oneupper' => '1',
    'onelower' => '1',
    'onenumber' => '1',
    'onespecial' => 
    array (
      'hidden' => '0',
    ),
    'SystemGeneratedPasswordON' => '',
    'generatepasswordtmpl' => 'aba0056c-6c95-d409-4a7d-4a834282ee76',
    'lostpasswordtmpl' => '',
    'customregex' => '',
    'regexcomment' => '',
    'forgotpasswordON' => false,
    'linkexpiration' => '1',
    'linkexpirationtime' => '30',
    'linkexpirationtype' => '1',
    'userexpiration' => '0',
    'userexpirationtime' => '',
    'userexpirationtype' => '1',
    'userexpirationlogin' => '',
    'systexpiration' => '0',
    'systexpirationtime' => '',
    'systexpirationtype' => '0',
    'systexpirationlogin' => '',
    'lockoutexpiration' => '0',
    'lockoutexpirationtime' => '',
    'lockoutexpirationtype' => '1',
    'lockoutexpirationlogin' => '',
    'forgotpassword' => false,
  ),
  'portal_view' => 'single_user',
  'require_accounts' => true,
  'resource_management' => 
  array (
    'special_query_limit' => 0,
    'special_query_modules' => 
    array (
      0 => 'Reports',
      1 => 'Export',
      2 => 'Import',
      3 => 'Administration',
      4 => 'Sync',
    ),
    'default_limit' => 0,
  ),
  'rss_cache_time' => '10800',
  'save_query' => 'populate_only',
  'session_dir' => '',
  'showDetailData' => true,
  'showThemePicker' => true,
  'site_url' => 'https://sugarinternal.sugarondemand.com',
  'slow_query_time_msec' => '100000',
  'snip_url' => 'http://67.207.131.175:20000/',
  'stack_trace_errors' => false,
  'sugar_version' => '6.1.0beta3',
  'time_formats' => 
  array (
    'H:i' => '23:00',
    'h:ia' => '11:00pm',
    'h:iA' => '11:00PM',
    'H.i' => '23.00',
    'h.ia' => '11.00pm',
    'h.iA' => '11.00PM',
  ),
  'timef' => 'H:i',
  'tmp_dir' => 'cache/xml/',
  'tracker_max_display_length' => 30,
  'translation_string_prefix' => false,
  'unique_key' => '9a60f4868ce9a7b846b45aa158c76129',
  'upload_badext' => 
  array (
    0 => 'php',
    1 => 'php3',
    2 => 'php4',
    3 => 'php5',
    4 => 'pl',
    5 => 'cgi',
    6 => 'py',
    7 => 'asp',
    8 => 'cfm',
    9 => 'js',
    10 => 'vbs',
    11 => 'html',
    12 => 'htm',
  ),
  'upload_dir' => 'cache/upload/',
  'upload_maxsize' => 30000000,
  'use_common_ml_dir' => false,
  'use_php_code_json' => true,
  'use_real_names' => false,
  'vcal_time' => '2',
  'verify_client_ip' => true,
  'wl_list_max_entries_per_page' => 10,
  'wl_list_max_entries_per_subpanel' => 3,
);
?>
