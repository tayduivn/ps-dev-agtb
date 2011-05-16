#!/usr/bin/python
import urlparse
import oauth2 as oauth
import urllib
import sys
import json
from pymongo import json_util

consumer_key = 'CONSUMERKEY'
consumer_secret = 'CONSUMERSECRET'

url = 'http://localhost:8888/sugar63/service/v4/rest.php'
urld = 'http://localhost:8888/sugar63/service/v4/rest.php?start_debug=1&debug_port=10137&debug_session_id=1012820&debug_host=127.0.0.1&debug_stop=1'

restparams = {
                'input_type': 'json',
                'request_type': 'json',
                'response_type': 'json',
                'method':'',
            }

consumer = oauth.Consumer(consumer_key, consumer_secret)
token = oauth.Token(sys.argv[1], sys.argv[2])
client = oauth.Client(consumer, token)

restparams['method'] = 'oauth_access'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print resp
print content
cont = json.loads(content)
sess = cont["id"]
print sess

restparams['method'] = 'get_available_modules'
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print resp
print content

restparams['method'] = 'get_entry_list'
param_string = {0:False, 1:'Calls', 2: '', 3:'', 4:0}
restparams['rest_data'] = json.dumps(param_string,sort_keys=False, default=json_util.default)
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print resp
print content

restparams['method'] = 'get_entry_list'
param_string = {0:False, 1:'Calls', 2: '', 3:'', 4:0}
restparams['rest_data'] = json.dumps(param_string,sort_keys=False, default=json_util.default)
resp, content = client.request(url, "POST", body=urllib.urlencode(restparams))
print resp
print content
