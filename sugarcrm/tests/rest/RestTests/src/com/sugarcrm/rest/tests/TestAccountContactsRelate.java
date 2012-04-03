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

public class TestAccountContactsRelate extends TestCase {

	@Test
	public void test_TestAccountContactsRelate () {
		String tmp = "";
		int status = -1;
		String uri = "";
		HttpPost post = null;
		HttpGet get = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		TestData testData = new TestData();
		String account_name = "New Rest Account Name";
		String existing_id = "a7365389-020c-d7f4-7513-4f5a84280ac0";
		
		data.put("username", testData.getValue("sugaruser"));
		data.put("password", testData.getValue("sugarpass"));
		data.put("type", "text");
		
		try {
			Date date = new Date();
			String time = String.valueOf(date.getTime());
			account_name = String.format("%s-%s", account_name, time);
			
			context = new BasicHttpContext();
			client = new DefaultHttpClient();
			Gson json = new Gson();
			String jsonString = json.toJson(data);
			entity = new StringEntity(jsonString);
			
			System.out.printf("Sending login info:\n");
			System.out.printf("JSON:\n%s\n", jsonString);
			
			entity = new StringEntity(jsonString, "application/json", "UTF-8");
			uri = String.format("%s/login", testData.getValue("sugarinst"));
			System.out.printf("URI: '%s'\n", uri);
			post = new HttpPost(uri);
			post.setEntity(entity);
			response = client.execute(post, context);
			HttpEntity responseData = response.getEntity();
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			String buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: %s\n", buffer);
			UserId id = (UserId)json.fromJson(buffer, UserId.class);
			System.out.printf("(*)TOKEN: %s\n", id.token);
			// end login //
						
			// get account from server and check some data //
			uri = String.format("%s/Accounts/%s/Contacts?fields=last_name,first_name,title", 
					testData.getValue("sugarinst"), existing_id);
			
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

			System.out.printf("(*)Finished getting Accounts Object...\n");
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
	} //Test New Rest Account-1330558008776
	
	public class UserId {
		private String token;
	}
}
