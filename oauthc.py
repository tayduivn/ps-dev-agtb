import urlparse
import oauth2 as oauth
import urllib
import sys

consumer_key = 'CONSUMERKEY'
consumer_secret = 'CONSUMERSECRET'

url = 'http://localhost:8888/sugarent/service/v3/rest.php'
authorize_url = 'http://localhost:8888/sugarent/index.php?module=Administration&action=OAuth'

restparams = {
                'input_type': 'json',
                'request_type': 'json',
                'method':'',
            }

consumer = oauth.Consumer(consumer_key, consumer_secret)
token = oauth.Token(sys.argv[1], sys.argv[2])
client = oauth.Client(consumer, token)

restparams['method'] = 'oauth_access'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print content
