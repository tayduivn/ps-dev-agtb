<?php
interface ExternalAPIPlugin {
    public function loadEAPM($eapmBean);
    public function checkLogin($eapmBean);
    public function logOff();
}