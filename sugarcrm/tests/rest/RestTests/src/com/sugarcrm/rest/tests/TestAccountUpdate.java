package com.sugarcrm.rest.tests;

import java.util.Date;
import java.util.HashMap;
import junit.framework.TestCase;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpDelete;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.client.methods.HttpPut;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.junit.Test;
import com.google.gson.Gson;

public class TestAccountUpdate extends TestCase {

	@Test
	public void test_TestAccountUpdate() {
		TestData testData = new TestData();
		String buffer = null;
		int status = -1;
		String uri = "";
		HttpPost post = null;
		HttpGet get = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		data.put("type", "text");
		String tmp;
		String account_name = "New Rest Account Name";
		String account_newName = account_name;
		
		try {
			Date date = new Date();
			String time = String.valueOf(date.getTime());
			account_name = String.format("%s-%s", account_name, time);
			data.put("username", testData.getValue("sugaruser"));
			data.put("password", testData.getValue("sugarpass"));
			
			context = new BasicHttpContext();
			client = new DefaultHttpClient();
			Gson json = new Gson();
			String jsonString = json.toJson(data);
			entity = new StringEntity(jsonString);
			
			System.out.printf("Sending login info:\n");
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
			
			// try to use Accounts object/module //
			System.out.printf("(*)Getting Accounts Object...\n");
			uri = String.format("%s/Accounts", testData.getValue("sugarinst"));

			System.out.printf("(*)URI: %s\n", uri);
			HashMap<String, String> postData = new HashMap<String, String>();
			
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
			date = new Date();
			time = String.valueOf(date.getTime());
			account_newName = String.format("%s", time);
			
			System.out.printf("(*)Getting Accounts Object...\n");
			uri = String.format("%s/Accounts/%s", testData.getValue("sugarinst"), accID.id);

			System.out.printf("(*)URI: %s\n\n", uri);
			HashMap<String, String> putData = new HashMap<String, String>();
			putData.put("name", account_newName);
			String putJson = json.toJson(putData);
			System.out.printf("PUT DATA: %s\n", putJson);
			HttpPut put = new HttpPut(uri);
			entity = new StringEntity(putJson, "application/json", "UTF-8");
			put.setEntity(entity);
			put.addHeader("OAuth-Token", id.token.toString());
			put.addHeader("User-Agent", "evilkook");
			
			response = client.execute(put);
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			responseData = response.getEntity();
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: '%s'\n\n", buffer);
			// finished with update //
			
			// read account back //
			uri = String.format("%s/Accounts/%s", testData.getValue("sugarinst"), accID.id);
			get = new HttpGet(uri);
			System.out.printf("URI: '%s'\n", uri);
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
			HashMap<String, Object> accountInfo = json.fromJson(buffer, HashMap.class);
			System.out.printf("RES-NAME: %s\n", accountInfo.get("name"));
			System.out.printf("OTH-NAME: %s\n", account_newName);
			
			assertEquals(account_newName, accountInfo.get("name"));
			//finished reading account //
			
			// delete new account to clean up //
			uri = String.format("%s/Accounts/%s", testData.getValue("sugarinst"), accID.id);
			HttpDelete del = new HttpDelete(uri);
			del.addHeader("OAuth-Token", id.token.toString());
			del.addHeader("User-Agent", "evilkook");
			response = client.execute(del);
			responseData = response.getEntity();
			TestUtils.bufferToString(responseData);
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
	}

	public class UserId {
		private String token;
	}
	
	public class AccountId {
		private String id;
	}
}
