package com.sugarcrm.rest.tests;

import java.io.File;
import java.io.FileWriter;
import java.util.Date;
import java.util.HashMap;
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
import junit.framework.TestCase;

public class MetaTest extends TestCase {

	@Test
	public void test_MetaTest() {
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
			
			uri = String.format("%s/metadata?filter=contacts", testData.getValue("sugarinst"));
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
			System.out.printf("(*)Finished getting ServerInfo Object...\n");

			System.out.printf("(*)Writing json file...\n");
			Date date = new Date();
			String time = String.valueOf(date.getTime());
			String filename = String.format("/Users/trichmond/%s.json", time);
			FileWriter fd = new FileWriter(new File(filename));
			buffer = TestUtils.bufferToString(responseData);
			fd.write(buffer);
			fd.close();
			System.out.printf("(*)Finished writing json file: %s\n", filename);
			System.out.printf("RESPONSE: '%s'\n", buffer);			
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
			
	}
	
	public class UserId {
		private String token;
	}
}
