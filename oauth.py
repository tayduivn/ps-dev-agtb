#!/usr/bin/python
import urlparse
import oauth2 as oauth
import urllib

consumer_key = 'CONSUMERKEY'
consumer_secret = 'CONSUMERSECRET'

url = 'http://localhost:8888/sugar63/service/v4/rest.php'

restparams = {
                'input_type': 'json',
                'request_type': 'json',
                'method':'',
            }

consumer = oauth.Consumer(consumer_key, consumer_secret)
client = oauth.Client(consumer)

# Step 1: Get a request token. This is a temporary token that is used for 
# having the user authorize an access token and to sign the request to obtain 
# said access token.

restparams['method'] = 'oauth_request_token'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
if resp['status'] != '200':
    print resp
    print content
    raise Exception("Invalid response %s." % resp['status'])

request_token = dict(urlparse.parse_qsl(content))
print "Request Token:"
print "    - oauth_token        = %s" % request_token['oauth_token']
print "    - oauth_token_secret = %s" % request_token['oauth_token_secret']
print 
authorize_url = request_token['authorize_url']

# Step 2: Redirect to the provider. Since this is a CLI script we do not 
# redirect. In a web application you would redirect the user to the URL
# below.

print "Go to the following link in your browser:"
print "%s&token=%s" % (authorize_url, request_token['oauth_token'])
print 
oauth_verifier = raw_input('What is the Verification Code? ')

if oauth_verifier == "":
	exit()

token = oauth.Token(request_token['oauth_token'],
    request_token['oauth_token_secret'])
token.set_verifier(oauth_verifier)
client = oauth.Client(consumer, token)

restparams['method'] = 'oauth_access_token'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))

access_token = dict(urlparse.parse_qsl(content))
print "Access Token:"
print "    - oauth_token        = %s" % access_token['oauth_token']
print "    - oauth_token_secret = %s" % access_token['oauth_token_secret']
print
print "You may now access protected resources using the access tokens above." 
print

token = oauth.Token(access_token['oauth_token'],
    access_token['oauth_token_secret'])
client = oauth.Client(consumer, token)

restparams['method'] = 'oauth_access'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print resp
print content
