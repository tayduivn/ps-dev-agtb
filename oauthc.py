import urlparse
import oauth2 as oauth
import urllib
import sys
import json
from pymongo import json_util

consumer_key = 'CONSUMERKEY'
consumer_secret = 'CONSUMERSECRET'

url = 'http://localhost:8888/sugarent/service/v3/rest.php'
urld = 'http://localhost:8888/sugarent/service/v3/rest.php?start_debug=1&debug_port=10137&debug_session_id=1012800&debug_host=127.0.0.1'
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

restparams['method'] = 'get_available_modules'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print content

restparams['method'] = 'get_entry_list'
param_string = {0:False, 1:'Calls', 2: '', 3:'', 4:0}
restparams['rest_data'] = json.dumps(param_string,sort_keys=False, default=json_util.default)
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print content
