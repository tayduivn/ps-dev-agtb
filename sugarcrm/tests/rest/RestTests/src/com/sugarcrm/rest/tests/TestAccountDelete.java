package com.sugarcrm.rest.tests;

import static org.junit.Assert.*;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.util.HashMap;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpDelete;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.junit.Test;
import com.google.gson.Gson;

public class TestAccountDelete {

	@Test
	public void test() {
		int status = -1;
		String uri = "";
		HttpPost post = null;
		HttpGet get = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		data.put("username", "admin");
		data.put("password", "admin");
		data.put("type", "text");
		
		try {
			context = new BasicHttpContext();
			client = new DefaultHttpClient();
			Gson json = new Gson();
			String jsonString = json.toJson(data);
			entity = new StringEntity(jsonString);
			System.out.printf("Sending login info:\n");
			System.out.printf("JSON:\n%s\n", jsonString);
			entity = new StringEntity(jsonString, "application/json", "UTF-8");
			uri = String.format("%s/login", TestData.BaseURL);
			post = new HttpPost(uri);
			post.setEntity(entity);
			response = client.execute(post, context);
			HttpEntity responseData = response.getEntity();
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				String tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			InputStreamReader in = new InputStreamReader(responseData.getContent());
			BufferedReader reader = new BufferedReader(in);
			
			String buffer = "";
			String tmp = "";
			while ((tmp = reader.readLine()) != null) {
				buffer = buffer + tmp;
			}
			
			System.out.printf("RESPONSE: %s\n", buffer);
			UserId id = (UserId)json.fromJson(buffer, UserId.class);
			System.out.printf("(*)TOKEN: %s\n", id.token);
			// end login //
			
			// try to use Accounts object/module //
			System.out.printf("(*)Getting Accounts Object...\n");
			uri = String.format("%s/Accounts", TestData.BaseURL);

			System.out.printf("(*)URI: %s\n\n", uri);
			HashMap<String, String> postData = new HashMap<String, String>();
			String name = String.valueOf(System.currentTimeMillis());
			name = String.format("New Rest Account-%s", name);
			System.out.printf("Account Name: %s\n", name);
			postData.put("name", name);
			postData.put("description", "Testing\n1\n2\n3...\nDONE>...");
			postData.put("phone_office", "7078314197771");
			postData.put("email1", "foo@bar.com");
			String postJson = json.toJson(postData);
			
			
			post = new HttpPost(uri);
			entity = new StringEntity(postJson, "application/json", "UTF-8");
			post.setEntity(entity);
			post.addHeader("OAuth Token", id.token.toString());
			post.addHeader("User-Agent", "evilkook");
			
			response = client.execute(post);
			responseData = response.getEntity();
			in = new InputStreamReader(responseData.getContent());
			reader = new BufferedReader(in);
			
			buffer = "";
			tmp = "";
			while ((tmp = reader.readLine()) != null) {
				buffer = buffer + tmp;
			}
			
			System.out.printf("RESPONSE: '%s'\n\n", buffer);
			
			AccountId accId = json.fromJson(buffer, AccountId.class);
			System.out.printf("(*)Account Id: '%s'\n", accId.id);
			
			uri = String.format("%s/Accounts/%s", TestData.BaseURL, accId.id);
			System.out.printf("URI: %s\n", uri);
			HttpDelete delete = new HttpDelete(uri);
			delete.addHeader("OAuth Token", id.token.toString());
			delete.addHeader("User-Agent", "evilkook");			
			response = client.execute(delete);
			responseData = response.getEntity();
			
			//if (response.getSx
			
			in = new InputStreamReader(responseData.getContent());
			reader = new BufferedReader(in);
			
			buffer = "";
			tmp = "";
			while ((tmp = reader.readLine()) != null) {
				buffer = buffer + tmp;
			}
			System.out.printf("RESPONSE: '%s'\n\n", buffer);
			
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
