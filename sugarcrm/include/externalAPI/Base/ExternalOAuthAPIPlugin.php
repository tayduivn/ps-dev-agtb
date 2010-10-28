<?php
interface ExternalOAuthAPIPlugin {
    public function getOauthParams();
    public function getOauthRequestURL();
    public function getOauthAuthURL();
    public function getOauthAccessURL();
}