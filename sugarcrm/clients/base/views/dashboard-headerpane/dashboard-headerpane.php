<?php
$viewdefs["base"]["view"]["dashboard-headerpane"] = array(
    "buttons" => array(
        array(
            "type" => "actiondropdown",
            "buttons" => array(
                array(
                    "name"      => "add_button",
                    "type"      => "rowaction",
                    "label"     => "LBL_CREATE_BUTTON_LABEL",
                    "css_class" => "btn",
                ),

                array(
                    "name"      => "edit_button",
                    "type"      => "rowaction",
                    "label"     => "LBL_EDIT_BUTTON",
                ),

                array(
                    "name"      => "collapse_button",
                    "type"      => "rowaction",
                    "label"     => "LBL_COLLAPSE_ALL",
                ),

                array(
                    "name"      => "expand_button",
                    "type"      => "rowaction",
                    "label"     => "LBL_EXPAND_ALL",
                ),
            ),
            "showOn" => "view",
        ),
        array(
            "name"      => "cancel_button",
            "type"      => "button",
            "label"     => "LBL_CANCEL_BUTTON_LABEL",
            "css_class" => "btn-invisible btn-link",
            "showOn" => "edit",
        ),
        array(
            "name"      => "delete_button",
            "type"      => "button",
            "label"     => "LBL_DELETE_BUTTON_LABEL",
            "css_class" => "btn-danger",
            "showOn" => "edit",
        ),
        array(
            "name"      => "save_button",
            "type"      => "button",
            "label"     => "LBL_SAVE_BUTTON_LABEL",
            "css_class" => "btn-primary",
            "showOn" => "edit",
        ),

        array(
            "name"      => "create_cancel_button",
            "type"      => "button",
            "label"     => "LBL_CANCEL_BUTTON_LABEL",
            "css_class" => "btn-invisible btn-link",
            "showOn" => "create",
        ),
        array(
            "name"      => "create_button",
            "type"      => "button",
            "label"     => "LBL_SAVE_BUTTON_LABEL",
            "css_class" => "btn-primary",
            "showOn" => "create",
        ),
    ),
    "panels" => array(
        array(
            "name" => "header",
            "fields" => array(
                array(
                    "type" => "dashboardtitle",
                    "name" => "name",
                    "placeholder" => "LBL_DASHBOARD_TITLE",
                ),
                array(
                    "type" => "layoutbutton",
                    "name" => "layout",
                ),
            )
        )
    )
);
