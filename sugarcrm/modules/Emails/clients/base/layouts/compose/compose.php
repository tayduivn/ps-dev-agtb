<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
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

$layout->push('main', array(
        'layout' => array(
            'type' => 'drawer',
            'showEvent' => array(
                "drawer:selection:fire",
                "drawer:create:fire",
            )
        ),
    ));

$layout->push('main', array('view'=>'compose'));
$layout->push('side', array('layout'=>'compose-sidebar'));
$viewdefs['Emails']['base']['layout']['compose'] = $layout->getLayout();
