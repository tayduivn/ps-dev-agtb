<?php
class EasyRpService
{
	// Replace $YOUR_DEVELOPER_KEY
	public function __construct($key)
	{
	    $this->key = $key;
	    $this->url = "https://www.googleapis.com/rpc?key=$key";
	}

	public static function getCurrentUrl() {
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		$url .= $_SERVER['SERVER_NAME'];
		if ($_SERVER['SERVER_PORT'] != '80') {
			$url .= ':'. $_SERVER['SERVER_PORT'];
		}
		$url .= $_SERVER['REQUEST_URI'];
		return $url;
	}

	private function post($postData)
	{
		$ch = curl_init();
		curl_setopt_array($ch, array(
		CURLOPT_URL => $this->url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
		CURLOPT_POSTFIELDS => json_encode($postData)));
		$response = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_code == '200' && !empty($response)) {
			return json_decode($response, true);
		}
		return NULL;
	}

	public function getUrl($email, $continueUri)
	{
	    $gitk['identifier'] = $email;
	    $gitk['openidRealm'] = 'Summer';
	    $gitk['continueUrl'] = $continueUri;
	    $gitk['oauthConsumerKey'] = $this->key;
	    $gitk['uiMode'] = "redirect";
	    // removed https://mail.google.com/mail/feed/atom  for now, not used
	    $gitk['oauthScope'] = "https://www.google.com/m8/feeds/ https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/userinfo.profile";
	    $gitk['context'] = array("rp_target" => "callback", "rp_purpose" => "signin","rp_input_email" => "email");
	    $gitk['access_type'] = 'offline';
	    $gitk['approval_prompt'] = 'force';

		$request['method'] = 'identitytoolkit.relyingparty.createAuthUrl';
		$request['apiVersion'] = 'v1';
		$request['params'] = $gitk;

		$result = $this->post($request);
		if (!empty($result['result'])) {
			return $result['result'];
		}
		return NULL;
	}

	public function verify($continueUri, $response)
	{
		$request = array();
		$request['method'] = 'identitytoolkit.relyingparty.verifyAssertion';
		$request['apiVersion'] = 'v1';
		$request['params'] = array();
		$request['params']['requestUri'] = $continueUri;
		$request['params']['postBody'] = $response;
		$request['params']['returnOauthToken'] = true;
		$request['params']['access_type'] = 'offline';

		$result = $this->post($request);
		if (!empty($result['result'])) {
			return $result['result'];
		}
		return NULL;
	}
}
