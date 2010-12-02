<?php
//if(0 && extension_loaded("oauth")) {
//    // use PHP native oauth
//    class SugarOAuth extends OAuth
//    {
//        public function getHttpClient()
//        {
//            // FIXME
//            throw new Exception("FIXME: getHttpClient not implemented!");
//        }
//    }
//} else {
    require_once 'Zend/Oauth/Consumer.php';
    // use ZF oauth
    class SugarOAuth extends Zend_Oauth_Consumer
    {
        protected $_last = '';
        protected $_oauth_config = array();

        public function __construct($consumer_key , $consumer_secret, $params = null)
        {
            $this->_oauth_config = array(
                'consumerKey' => $consumer_key,
                'consumerSecret' => $consumer_secret,
            );
            if(!empty($params)) {
                $this->_oauth_config = array_merge($this->_oauth_config, $params);
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

        public function getRequestToken($url, $callback = null, $params = array())
        {
            if(!empty($callback)) {
                $this->setCallbackUrl($callback);
            }
            list($clean_url, $query) = explode('?', $url);
            if($query) {
                $url = $clean_url;
                parse_str($query, $query_params);
                $params = array_merge($params, $query_params);
            }
            $this->setRequestTokenUrl($url);
            $this->_last = $token = parent::getRequestToken($params);
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
            list($clean_url, $query) = explode('?', $url);
            if($query) {
                $url = $clean_url;
                parse_str($query, $query_params);
                $params = array_merge($params?$params:array(), $query_params);
            }
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
            $this->_lastReq = $client->getLastRequest();
            return $resp->getBody();
       }

       public function getClient()
       {
            $acc = $this->makeAccessToken();
            return $acc->getHttpClient($this->_oauth_config);
       }

       public function getLastResponse()
       {
            return $this->_last;
       }

       public function getLastRequest()
       {
            return $this->_lastReq;
       }
    }
