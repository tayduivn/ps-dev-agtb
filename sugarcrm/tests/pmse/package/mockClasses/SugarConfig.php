<?php


class SugarConfig {
    public static function getInstance() {
        return new SugarConfig();

    }
    public static function get($string, $value) {
         return $value;
    }
}
