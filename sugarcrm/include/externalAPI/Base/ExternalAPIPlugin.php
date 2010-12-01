<?php
interface ExternalAPIPlugin {
    public function supports($method = '');
    public function loadEAPM($eapmBean);
    public function checkLogin($eapmBean);
    public function logOff();
}