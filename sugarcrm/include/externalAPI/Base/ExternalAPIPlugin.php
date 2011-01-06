<?php
interface ExternalAPIPlugin {
    public function supports($method = '');
    public function loadEAPM($eapmBean);
    public function checkLogin($eapmBean = null);
    public function logOff();
}