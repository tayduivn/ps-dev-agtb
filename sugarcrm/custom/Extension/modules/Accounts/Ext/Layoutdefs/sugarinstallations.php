<?php

                //BEGIN Sugar Interal customizations
/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$layout_defs['Accounts']['subpanel_setup']['download_keys'] = 
                array(
                        'order' => 65,
                        'module' => 'DownloadKeys',
                        'sort_order' => 'asc',
                        'sort_by' => 'download_key',
                        'subpanel_name' => 'default',
                        'get_subpanel_data' => 'download_keys',
                        'add_subpanel_data' => 'download_key_id',
                        'title_key' => 'LBL_DOWNLOAD_KEYS_SUBPANEL_TITLE',
                        'top_buttons' => array(
                                array('widget_class' => 'SubPanelTopCreateButton'),
                        ),
                );
*/

$layout_defs['Accounts']['subpanel_setup']['sugar_installations'] = 
                array(
                        'order' => 66,
                        'sort_order' => 'asc',
                        'sort_by' => 'status',
                        'module' => 'SugarInstallations',
                        'subpanel_name' => 'default',
                        'get_subpanel_data' => 'sugar_installations',
                        'add_subpanel_data' => 'sugar_installation_id',
                        'title_key' => 'LBL_SUGAR_INSTALLATIONS_SUBPANEL_TITLE',
                        'top_buttons' => array(),
                );
                //END Sugar Interal customizations


?>
