<?php
require_once('pardotData.abstract.php');
class pardotVisitor extends pardotData {
    var $id;
    var $browser;
    var $browser_version;
    var $operating_system;
    var $operating_system_version;
    var $language;
    var $screen_height;
    var $screen_width;
    var $page_view_count;
    var $is_flash_enabled;
    var $is_java_enabled;
    var $ip_address;
    var $hostname;
    var $campaign_parameter;
    var $medium_parameter;
    var $source_parameter;
    var $content_parameter;
    var $term_parameter;
    var $created_at;
}