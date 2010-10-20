<?php
interface ExternalAPIPlugin {
    public function loadEAPM($eapmData);
    public function checkLogin();
    public function logOff();
}