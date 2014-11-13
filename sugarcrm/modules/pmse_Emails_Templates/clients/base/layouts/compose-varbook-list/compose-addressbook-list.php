<?php

$viewdefs['pmse_Emails_Templates']['base']['layout']['compose-varbook-list'] = array(
    "type" => "list",
    'components' =>
    array(
        array(
            'view' => 'compose-varbook-list',
        ),
        array(
            'view' => 'compose-varbook-list-bottom',
        ),
    ),
    'type' => 'compose-varbook-list',
    'span' => 12,
);

