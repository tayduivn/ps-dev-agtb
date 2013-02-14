<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
/*
 * //TODO Refactor Address to launch a drawer using the app.drawer.open
$layout->push("main", array(
                           "layout"         => array(
                               "type"      => "drawer",
                               "module"    => "Emails",
                               "name"      => "compose-addressbook-drawer",
                               "showEvent" => array(
                                   "compose:addressbook:open",
                               ),
                               "components" => array(
                                   array(
                                       "layout"  => "compose-addressbook",
                                       "context" => array(
                                           "module" => "Emails",
                                           "mixed"  => true,
                                       ),
                                   ),
                               ),
                           ),
                      ));
*/
$layout->push('main', array('view'=>'compose'));
$viewdefs['Emails']['base']['layout']['compose'] = $layout->getLayout();
