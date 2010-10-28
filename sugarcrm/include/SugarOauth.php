<?php
if(0 && extension_loaded("oauth")) {
    // use PHP native oauth
    class SugarOAuth extends OAuth {
    }
} else {
    require_once 'Zend/Oauth/Consumer.php';
    // use ZF oauth
    class SugarOAuth extends Zend_Oauth_Consumer
    {
        protected $_last = '';
        protected $_oauth_config = array();

        public function __construct($consumer_key , $consumer_secret, $signature_method = null, $auth_type = null)
        {
            $this->_oauth_config = array(
                'consumerKey' => $consumer_key,
                'consumerSecret' => $consumer_secret,
            );
            if(!empty($signature_method)) {
                $this->_oauth_config['signatureMethod'] = $signature_method;
            }
            parent::__construct($this->_oauth_config);
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
            $this->_last = $token = parent::getRequestToken();
            return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
        }

        public function getAccessToken($url)
        {
            $this->setAccessTokenUrl($url);
            $this->_last = $token = parent::getAccessToken($_REQUEST, $this->makeRequestToken());
            return array('oauth_token' => $token->getToken(), 'oauth_token_secret' => $token->getTokenSecret());
        }

       public function fetch($url, $params = null, $method = 'GET', $headers = null)
       {
            $acc = $this->makeAccessToken();
            $client = $acc->getHttpClient($this->_oauth_config, $url);
            $client->setMethod($method);
            if(!empty($headers)) {
                $client->setHeaders($headers);
            }
            if(!empty($params)) {
                if($method == 'GET') {
                    $client->setParameterGet($params);
                } else {
                    $client->setParameterPost($params);
                }
            }
            $this->_last = $resp = $client->request();
            return $resp->getBody();
       }

       public function getLastResponse()
       {
            return $this->_last;
       }

//        public function __call($method, $args)
//        {
//            parent::
//            debug_print_backtrace();
//            die("SugarOAUTH: unsupported method $method called");
//            return false;
//        }
    }
}