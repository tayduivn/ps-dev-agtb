<?php

function get_62x_files_to_remove($original_ver, $target_ver)
{

$files = array();

// In 6.2.2 we did the following
// 1) Removed include/JSON.js
// 2) Removed include/jsolait files
// 3) Upgraded more YUI 3 libraries
// 4) Upgraded TinyMCE from 2.x to 3.x version
// We will additionally clean up the legacy include/utils/external_cache direcotry

if($original_ver < '622')
{
	$files[] = 'include/utils/external_cache';
	$files[] = 'include/jsolait';
	$files[] = 'include/JSON.js';
	$files[] = 'include/javascript/tiny_mce/plugins/compat2x/editor_plugin.js';
	$files[] = 'include/javascript/tiny_mce/plugins/compat2x/editor_plugin_src.js';
	$files[] = 'include/javascript/tiny_mce/plugins/media/css/content.css';
	$files[] = 'include/javascript/tiny_mce/plugins/media/img';
	$files[] = 'include/javascript/tiny_mce/plugins/pagebreak';
	$files[] = 'include/javascript/tiny_mce/plugins/paste';
	$files[] = 'include/javascript/tiny_mce/plugins/safari';
	$files[] = 'include/javascript/yui3/build/cssgrids/grids-context-min.css';
	$files[] = 'include/javascript/yui3/build/cssgrids/grids-context.css';
	$files[] = 'include/javascript/yui3/build/get/get-min.js';
	$files[] = 'include/javascript/yui3/build/get/get.js';
	$files[] = 'include/javascript/yui3/build/node/node-aria-min.js';
	$files[] = 'include/javascript/yui3/build/node/node-aria.js';
	$files[] = 'include/javascript/yui3/build/widget/widget-position-ext-min.js';
	$files[] = 'include/javascript/yui3/build/widget/widget-position-ext.js';
	$files[] = 'include/javascript/yui3/build/yui-base/yui-base-min.js';
	$files[] = 'include/javascript/yui3/build/yui-base/yui-base.js';
	$files[] = 'include/javascript/yui/build/connection/connection_core-debug.js';
	$files[] = 'include/javascript/yui/build/datemath/datemath-debug.js';
	$files[] = 'include/javascript/yui/build/element-delegate/element-delegate-debug.js';
	$files[] = 'include/javascript/yui/build/event-delegate/event-delegate-debug.js';
	$files[] = 'include/javascript/yui/build/event-mouseenter/event-mouseenter-debug.js';
	$files[] = 'include/javascript/yui/build/event-simulate/event-simulate-debug.js';
	$files[] = 'include/javascript/yui/build/progressbar/progressbar-debug.js';
	$files[] = 'include/javascript/yui/build/storage/storage-debug.js';
	$files[] = 'include/javascript/yui/build/stylesheet/stylesheet-debug.js';
	$files[] = 'include/javascript/yui/build/swf/swf-debug.js';
	$files[] = 'include/javascript/yui/build/swfdetect/swfdetect-debug.js';
	$files[] = 'include/javascript/yui/build/swfstore/swf.js';
	$files[] = 'include/javascript/yui/build/swfstore/swfstore-debug.js';
	$files[] = 'jssource/src_files/include/javascript/jsolait';
	$files[] = 'modules/Activities/OpenListView.html';
	$files[] = 'modules/Activities/OpenListView.php';
}

return $files;

}
?>