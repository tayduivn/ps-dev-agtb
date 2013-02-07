<?php
$viewdefs["Emails"]["base"]["view"]["compose-addressbook-recipientscontainer"] = array(
    "type"      => "edit",
    "panels"    => array(
        array(
            "fields" => array(
                array(
                    "name"                => "compose_addressbook_selected_recipients",
                    "type"                => "recipients",
                    "label"               => "LBL_SELECTED_RECIPIENTS",
                    "css_class_container" => "controls-one btn-fit",
                    "hide_address_book"   => true,
                ),
            ),
        ),
    ),
);
