<?php
if(extension_loaded("oauth")) {
    // use PHP native oauth
    class SugarOauth extends OAuth {
    }
} else {
    require_once 'Zend/Oauth/Consumer.php';
    // use ZF oauth
    class SugarOauth extends Zend_Oauth_Consumer {
        public function __construct($consumer_key , $consumer_secret, $signature_method, $auth_type)
        {
            $config = array(
                'consumerKey' => $consumer_key,
                'consumerSecret' => $consumer_secret,
            );
            parent::__construct($config);
        }

        public function enableDebug()
        {
            return $this;
        }

        public function setToken($token, $secret)
        {
            $this->token = array($token, $secret);
        }

        public function makeRequestToken()
        {
            $token = new Zend_Oauth_Token_Request();
            $token->setToken($this->token[0]);
            $token->setTokenSecret($this->token[1]);
            return $token;
        }

        public function makeAccessToken()
        {
            $token = new Zend_Oauth_Token_Access();
            $token->setToken($this->token[0]);
            $token->setTokenSecret($this->token[1]);
            return $token;
        }

        public function getRequestToken($url, $callback = null)
        {
            if(!empty($callback)) {
                $this->setCallbackUrl($callback);
            }
            $this->setRequestTokenUrl($url);
            $token = parent::getRequestToken();
            return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
        }

        public function getAccessToken($url)
        {
            $this->setAccessTokenUrl($url);
            $token = parent::getAccessToken($_REQUEST, $this->makeRequestToken());
            return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
        }

       public function fetch($url, $params = null, $method = Zend_Http_Client::GET, $headers = null)
       {
            $acc = $this->makeAccessToken();
            $config = array(
                'consumerKey' => $this->getConsumerKey(),
                'consumerSecret' => $this->getConsumerSecret(),
            );
            $client = $acc->getHttpClient($config, $url);
            $client->setMethod($method);
            if(!empty($headers)) {
                $client->setHeaders($headers);
            }
            if(!empty($params)) {
                // FIXME: post too
                $client->setParameterGet($params);
            }
            $resp = $client->request();
            return $resp->getBody();
       }

        public function __call($method, $args)
        {
            $GLOBALS['log']->fatal("SugarOAUTH: unsupported method $method called");
            return false;
        }
    }
}