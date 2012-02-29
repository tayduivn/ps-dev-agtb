package com.sugarcrm.rest.tests;

import java.util.Date;
import java.util.HashMap;
import junit.framework.TestCase;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.junit.Test;
import com.google.gson.Gson;

public class TestAccountObjects extends TestCase {

	@Test
	public void test_TestAccountObjects() {
		int status = -1;
		String uri = "";
		String tmp = "";
		HttpPost post = null;
		HttpGet get = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		data.put("type", "text");
		String buffer = "";
		String account_name = "Testing Rest Acount";
		TestData testData = null;
		Date date = null;
		String time = "";
		
		try {
			testData = new TestData();
			data.put("username", testData.getValue("sugaruser"));
			data.put("password", testData.getValue("sugarpass"));
			
			// begin login //
			context = new BasicHttpContext();
			client = new DefaultHttpClient();
			Gson json = new Gson();
			String jsonString = json.toJson(data);
			entity = new StringEntity(jsonString);
			System.out.printf("Sending login info:\n");
			System.out.printf("JSON:\n%s\n", jsonString);
			entity = new StringEntity(jsonString, "application/json", "UTF-8");
			uri = String.format("%s/login", testData.getValue("sugarinst"));
			post = new HttpPost(uri);
			post.setEntity(entity);
			response = client.execute(post, context);
			HttpEntity responseData = response.getEntity();
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: %s\n", buffer);
			UserId id = (UserId)json.fromJson(buffer, UserId.class);
			System.out.printf("(*)TOKEN: %s\n", id.token);
			// end login //
			
			// create account just to make sure we got something in there //
			System.out.printf("(*)Getting Accounts Object...\n");
			uri = String.format("%s/Accounts", testData.getValue("sugarinst"));

			System.out.printf("(*)URI: %s\n", uri);
			HashMap<String, String> postData = new HashMap<String, String>();
			
			date = new Date();
			time = String.valueOf(date.getTime());
			account_name = String.format("%s-%s", account_name, time);
			postData.put("name", account_name);
			postData.put("description", "Testing\n1\n2\n3...\nDONE>...");
			postData.put("phone_office", "7078314197771");
			postData.put("email1", "foo@bar.com");
			String postJson = json.toJson(postData);
			
			// create new account //
			post = new HttpPost(uri);
			entity = new StringEntity(postJson, "application/json", "UTF-8");
			post.setEntity(entity);
			post.addHeader("OAuth-Token", id.token.toString());
			post.addHeader("User-Agent", "evilkook");
			
			response = client.execute(post);
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			responseData = response.getEntity();
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: '%s'\n\n", buffer);
			AccountId accID = json.fromJson(buffer, AccountId.class);
			System.out.printf("New Account ID: '%s'\n", accID.id);
			// finished account create //
			
			// try to use Accounts object/module //
			System.out.printf("(*)Getting Accounts Object...\n");
			uri = String.format("%s/accounts", testData.getValue("sugarinst"));

			System.out.printf("(*)URI: %s\n\n", uri);
			get = new HttpGet(uri);
			get.addHeader("OAuth-Token", id.token.toString());
			get.addHeader("User-Agent", "evilkook");
			response = client.execute(get);
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			responseData = response.getEntity();
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: '%s'\n\n", buffer);
			@SuppressWarnings("unchecked")
			HashMap<String, Object> accdata = json.fromJson(buffer, HashMap.class);
			@SuppressWarnings("unchecked")
			HashMap<String, Object> accList = (HashMap<String, Object>)accdata.get("ids");
			String[] keys = accList.keySet().toArray(new String[0]);
			int found = -1;
			for (int i = 0; i <= keys.length -1; i++) {
				if (keys[i].equalsIgnoreCase(accID.id)) {
					found = i;
					break;
				}
			}

			assertFalse(-1 == found);
			assertEquals(accID.id, keys[found]);
			System.out.printf("(*)Finished getting Accounts Object...\n");
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
	}
	
	public class AccountId {
		private String id;
	}
	
	public class UserId {
		private String token;
	}
}
