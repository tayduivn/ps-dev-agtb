package com.sugarcrm.rest.tests;

import static org.junit.Assert.*;
import java.util.Arrays;
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

public class TestServerInfo extends TestCase {

	@Test
	public void test_TestServerInfo() {
		int status = -1;
		String uri = "";
		HttpPost post = null;
		HttpGet get = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		TestData testData = null;
		String buffer = "";
		
		try {
			testData = new TestData();
			data.put("username", testData.getValue("sugaruser"));
			data.put("password", testData.getValue("sugarpass"));
			data.put("type", "text");
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
			
			System.out.printf("Getting ServerInfo...\n");
			response = client.execute(post, context);
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				String tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			HttpEntity responseData = response.getEntity();
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: %s\n", buffer);
			UserId id = (UserId)json.fromJson(buffer, UserId.class);
			System.out.printf("(*)TOKEN: %s\n", id.token);
			// end login //
			
			uri = String.format("%s/serverinfo", testData.getValue("sugarinst"));
			System.out.printf("(*)URI: %s\n", uri);
			get = new HttpGet(uri);
			get.addHeader("OAuth Token", id.token.toString());
			get.addHeader("User-Agent", "evilkook");
			response = client.execute(get);
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				String tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				fail(tmp);
			}
			
			responseData = response.getEntity();
			buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: '%s'\n", buffer);
			System.out.printf("(*)Finished getting ServerInfo Object...\n");

			@SuppressWarnings("unchecked")
			HashMap<String, String> srvInfo = json.fromJson(buffer, HashMap.class);
			String[] expectKeys = {
				"time_type", "time", "version", "flavor", "md5"	
			};
			
			Arrays.sort(expectKeys);
			String[] keyset = srvInfo.keySet().toArray(new String[0]);
			Arrays.sort(keyset);
			assertArrayEquals(expectKeys, keyset);

			for (int i = 0; i <= keyset.length -1; i++) {
				String val = null;
				val = keyset[i];
				assertNotNull(val);
			}
			
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
		
		
	}

	public class UserId {
		private String token;
	}
	
}
