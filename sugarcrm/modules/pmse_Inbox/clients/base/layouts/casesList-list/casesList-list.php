<?php

$viewdefs['pmse_Inbox']['base']['layout']['casesList-list'] = array(
    "type" => "list",
    'components' =>
        array(
            array(
                'view' => 'casesList-list',
            ),
            array(
                'view' => 'casesList-list-bottom',
            ),
        ),
    'type' => 'casesList-list',
    'span' => 12,
);
