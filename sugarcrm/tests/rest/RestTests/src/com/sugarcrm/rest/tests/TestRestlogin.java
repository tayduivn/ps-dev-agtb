package com.sugarcrm.rest.tests;

import static org.junit.Assert.*;
import java.io.BufferedReader;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.util.HashMap;
import org.apache.http.HttpEntity;
import org.apache.http.HttpResponse;
import org.apache.http.client.methods.HttpGet;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import org.apache.http.protocol.HttpContext;
import org.apache.http.util.EntityUtils;
import org.junit.Test;
import com.google.gson.Gson;


public class TestRestlogin {
	
	private String BaseURL = "http://localhost:8888/sugar/ent/sugarcrm/rest";
	private HashMap<String, Object> data = null;
	
	//@Test
	public void test() {
		boolean result = false;
		HttpPost post = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		String uri = String.format("%s/login", this.BaseURL);
		StringEntity entity;
		String jsonString = "";
		int status = -1;
		
		data = new HashMap<String, Object>();
		
		try {
			data.put("username", "admin");
			data.put("password", "admin");
			data.put("type", "text");
			
			context = new BasicHttpContext();
			client = new DefaultHttpClient();
			
			Gson json = new Gson();
			jsonString = json.toJson(data);
			entity = new StringEntity(jsonString);
			
			System.out.printf("JSON:\n%s\n", jsonString);
			
			entity = new StringEntity(jsonString, "application/json", "UTF-8");
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
			// finish login //
			
			// get metadata //
			// Authorization: OAuth token
			uri = String.format("%s/metadata", BaseURL);
			HttpGet get = new HttpGet(uri);
			get.addHeader("OAuth Token", id.token.toString());
			get.addHeader("User-Agent", "evilkook");
			response = client.execute(get);
			responseData = response.getEntity();
			//this.PrintResponse(responseData.getContent());
			EntityUtils.consume(responseData);
			
			// get objects //
			uri = String.format("%s", BaseURL);
			get = new HttpGet(uri);
			get.addHeader("OAuth Token", id.token.toString());
			get.addHeader("User-Agent", "evilkook");
			response = client.execute(get);
			responseData = response.getEntity();
			this.PrintResponse(responseData.getContent());
			EntityUtils.consume(responseData);
			
			// logout now //
			data = new HashMap<String, Object>();
			data.put("token", id.token);
			json = new Gson();
			jsonString = json.toJson(data);
			System.out.printf("(*)SENDING JSON: %s\n", jsonString);
			entity = new StringEntity(jsonString, "application/json", "UTF-8");
			uri = String.format("%s/logout", this.BaseURL);
			post = new HttpPost(uri);
			post.setEntity(entity);
			response = client.execute(post, context);
			responseData = response.getEntity();
			status = response.getStatusLine().getStatusCode();
			
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			} else {
				System.out.printf("Logged out...\n");
			}
			
			this.PrintResponse(responseData.getContent());
			
			result = true;
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
			result = false;
		}
		
		
		if (!result) {
			fail("Test Failed...");
		}
		
	}
	
	private void PrintResponse(InputStream stream) {
		try  {
			InputStreamReader in = new InputStreamReader(stream);
			BufferedReader reader = new BufferedReader(in);
			
			String buffer = "";
			String tmp = "";
			while ((tmp = reader.readLine()) != null) {
				buffer = buffer + tmp;
			}
			
			System.out.printf("%s\n", buffer);
			
			stream.close();
		} catch (Exception exp) {
			exp.printStackTrace();
		}
		
	}
	
	public class UserId {
		private String token;
	}
	
}

