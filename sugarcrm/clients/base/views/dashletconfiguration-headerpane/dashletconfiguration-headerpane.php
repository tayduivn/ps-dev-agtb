<?php
$viewdefs["base"]["view"]["dashletconfiguration-headerpane"] = array(
    "buttons" => array(
        array(
            "name"      => "cancel_button",
            "type"      => "button",
            "label"     => "LBL_CANCEL_BUTTON_LABEL",
            "css_class" => "btn-invisible btn-link",
        ),
        array(
            "name"      => "save_button",
            "type"      => "button",
            "label"     => "LBL_SAVE_BUTTON_LABEL",
            "css_class" => "btn-primary",
        ),
    ),
    "panels" => array(
        array(
            "name" => "header",
            "fields" => array(
                array(
                    "type" => "base",
                    "name" => "label",
                    "placeholder" => "LBL_NAME",
                ),
            )
        )
    )
);
