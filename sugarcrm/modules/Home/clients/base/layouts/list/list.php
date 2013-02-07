<?php
/*
$layout = MetaDataManager::getLayout('GenericLayout');

// first col
$layout->push(
    array(
        'width' => 5,
        'rows' => array(
            array(
                array(
                    'width' => 12,
                ),
            ),
            array(
                array(
                    'width' => 4,
                ),
                array(
                    'width' => 4,
                ),
                array(
                    'width' => 4,
                    'name' => 'My Contacts',
                    'view' => 'list',
                    'context' => array(
                        'module' => 'Contacts',
                    ),
                ),
            ),
        ),
    )
);
$layout->push(
    array(
        'width' => 7,
        'rows' => array(
            array(
                array(
                    'width' => 6,
                ),
                array(
                    'width' => 6,
                ),
            ),
            array(
                array(
                    'width' => 12,
                ),
            ),
            array(
                array(
                    'width' => 4,
                ),
                array(
                    'width' => 4,
                ),
                array(
                    'width' => 4,
                ),
            ),
        ),
    )
);
*/


//$layout->push(
//    array(
//            array(
//                'name' => 'My Accounts',
//                'view' => 'list',
//                'context' => array(
//                    'module' => 'Accounts',
//                ),
//                'width' => 12,
//            ),
//    )
//);

//$layout->push(
//    array(
//            array(
//                'name' => 'My Contacts',
//                'view' => 'list',
//                'context' => array(
//                    'module' => 'Contacts',
//                ),
//                'width' => 12,
//            ),
//    )
//);

// TODO make a Dashboard layout that extends from GenericLayout
/*
$viewdefs['Home']['base']['layout']['list'] = $layout->getLayout();
$viewdefs['Home']['base']['layout']['list']['type'] = 'dashboard';
$viewdefs['Home']['base']['layout']['list']['css_class'] = 'dashboard';
*/
$layout = MetaDataManager::getLayout("GenericLayout");
$layout->push(array("view" => "dashboard-headerpane"));
//$layout->push(array("layout" => "dashlets"));

$viewdefs['Home']['base']['layout']['list'] = $layout->getLayout();
$viewdefs['Home']['base']['layout']['list']['type'] = 'dashboard';
