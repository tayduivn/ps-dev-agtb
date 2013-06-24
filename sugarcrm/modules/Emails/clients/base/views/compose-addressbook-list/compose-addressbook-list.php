<?php
$viewdefs["Emails"]["base"]["view"]["compose-addressbook-list"] = array(
    'use_template' => 'list',
    "selection" => array(
        "type"    => "multi",
        "actions" => array(),
    ),
    "panels"    => array(
        array(
            "fields" => array(
                array(
                    "name"  => "name",
                    "label" => "LBL_LIST_NAME",
                ),
                array(
                    "name"     => "email",
                    "label"    => "LBL_LIST_EMAIL",
                    "type"     => "email",
                    "sortable" => true,
                ),
                array(
                    "name"     => "module",
                    "label"    => "LBL_MODULE",
                    "sortable" => false,
                ),
            ),
        ),
    ),
);
