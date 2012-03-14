package com.sugarcrm.rest.tests;

import static org.junit.Assert.*;
import java.util.ArrayList;
import java.util.Date;
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

public class TestAccountPagnation {

	@Test
	public void test() {
		String tmp = "";
		int status = -1;
		String uri = "";
		HttpPost post = null;
		HttpContext context;
		HttpResponse response;
		DefaultHttpClient client = null;
		StringEntity entity;
		HashMap<String, Object> data = new HashMap<String, Object>();
		TestData testData = new TestData();
		String account_name = "New Rest Account Name";
		
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
			
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
				System.out.printf("%s\n", tmp);
				fail(tmp);
			}
			
			HttpEntity responseData = response.getEntity();
			String buffer = TestUtils.bufferToString(responseData);
			System.out.printf("RESPONSE: %s\n", buffer);
			
			UserId id = (UserId)json.fromJson(buffer, UserId.class);
			System.out.printf("(*)TOKEN: %s\n", id.token);
			// end login //

			// delete all accounts //
			this.deleteAccounts(id.token);
			// end delete all accounts //
			
			// creating accounts //
			this.createAccounts(id.token);
			// end accoutns create //
			
			// paging //
			this.pageAccounts(id.token);
			// end paging //
			
			// delete all accounts //
			this.deleteAccounts(id.token);
			// end delete all accounts //
			
		} catch (Exception exp) {
			exp.printStackTrace();
			fail(exp.getMessage());
		}
	}

	@SuppressWarnings("unchecked")
	private void pageAccounts(String token) {
		int bucketSize = 5;
		double offset = 0;
		TestData testData = new TestData();
		String buffer = null;
		Gson json = new Gson();
		String uri = String.format("%s/accounts?fields=id&maxresult=%d&offset=%d", 
				testData.getValue("sugarinst"), bucketSize, (int)offset);
		HttpGet get = null;
		HttpResponse response = null;
		DefaultHttpClient client = new DefaultHttpClient();
		ArrayList<String> pagedIds = new ArrayList<String>();
		
		try {
			do {
				uri = String.format("%s/accounts?fields=id&maxresult=%d&offset=%d", 
						testData.getValue("sugarinst"), bucketSize, (int)offset);
				System.out.printf("URI: '%s'\n", uri);
				get = new HttpGet(uri);
				get.addHeader("OAuth-Token", token);
				get.addHeader("User-Agent", "evilkook");
				
				response = client.execute(get);
				buffer = TestUtils.bufferToString(response.getEntity());
				System.out.printf("PAGE: '%s'\n", buffer);
				HashMap<String, Object> data = json.fromJson(buffer, HashMap.class);
				
				//double result_count = Double.valueOf((Double)data.get("result_count"));
				double next_offset = Double.valueOf((Double)data.get("next_offset"));
				ArrayList<HashMap<String, String>> tmp_data = (ArrayList<HashMap<String, String>>)data.get("records");
				
				for (int x = 0; x <= tmp_data.size() -1; x++) {
					if (tmp_data.get(x).containsKey("id")) {
						pagedIds.add(tmp_data.get(x).get("id"));
					}
				}
				
				offset = next_offset;		
			} while (offset != 0);
			
			uri = String.format("%s/accounts?fields=id&maxresult=9999999", 
					testData.getValue("sugarinst"));
			System.out.printf("URI: '%s'\n", uri);
			get = new HttpGet(uri);
			get.addHeader("OAuth-Token", token);
			get.addHeader("User-Agent", "evilkook");
			
			response = client.execute(get);
			buffer = TestUtils.bufferToString(response.getEntity());
			System.out.printf("PAGE: '%s'\n", buffer);
			HashMap<String, Object> data = json.fromJson(buffer, HashMap.class);
			ArrayList<String> waddedIds = new ArrayList<String>();
			ArrayList<HashMap<String, String>> tmp_data = (ArrayList<HashMap<String, String>>)data.get("records");
			
			for (int x = 0; x <= tmp_data.size() -1; x++) {
				waddedIds.add(tmp_data.get(x).get("id"));
			}
			
			boolean ismatch = pagedIds.containsAll(waddedIds);
			
			assertTrue(ismatch);
		} catch (Exception exp) {
			exp.printStackTrace();
		}
	}
	
	private void deleteAccounts(String token) {
		TestData testData = new TestData();
		String tmp = null;
		String buffer = null;
		Gson json = new Gson();
		String uri = String.format("%s/accounts?fields=id&maxresult=999999", 
				testData.getValue("sugarinst"));
		HttpGet get = null;
		HttpResponse response = null;
		int status;
		DefaultHttpClient client = new DefaultHttpClient();
		
		get = new HttpGet(uri);
		get.addHeader("OAuth-Token", token);
		get.addHeader("User-Agent", "evilkook");
		
		try {
			response = client.execute(get);
		} catch (Exception exp) {
			exp.printStackTrace();
		}
		
		status = response.getStatusLine().getStatusCode();
		if (status != 200) {
			tmp = String.format("Error: Status Code is '%d', was expecting: '200'!", status);
			fail(tmp);
		}
		
		buffer = TestUtils.bufferToString(response.getEntity());
		
		@SuppressWarnings("unchecked")
		HashMap<String, Object> accdata = json.fromJson(buffer, HashMap.class);
		@SuppressWarnings("unchecked")		
		ArrayList<HashMap<String, String>> ids = (ArrayList<HashMap<String, String>>)accdata.get("records");
		
		for (int i = 0; i <= ids.size() -1; i++) {  
			HashMap<String, String> d1 = ids.get(i);
			String value = d1.get("id").toString();
			this.deleteSingleAccount(value, token);
		}
	}
	
	private void deleteSingleAccount(String accountID, String token) {
		TestData testData = new TestData();
		String uri = String.format("%s/accounts/%s", testData.getValue("sugarinst"), accountID);
		HttpResponse response = null;
		int status;
		DefaultHttpClient client = new DefaultHttpClient();
		
		System.out.printf("Deleting Account: %s\n", accountID);
		
		HttpDelete del = new HttpDelete(uri);
		del.addHeader("User-Agent", "evilkook");
		del.addHeader("OAuth-Token", token);
		
		try {
			response = client.execute(del);
			status = response.getStatusLine().getStatusCode();
			if (status != 200) {
				System.out.printf("Error: '%d' trying to delete account: '%s'\n", status, accountID);
			}
		} catch (Exception exp) {
			exp.printStackTrace();
		}
	}
	
	private ArrayList<String> createAccounts(String token) {
		ArrayList<String> result = new ArrayList<String>();
		DefaultHttpClient client = null;
		int i = 0;
		int max = 200;
		Date date;
		String time;
		Gson json = new Gson();
		TestData testData = new TestData();
		String uri = "";
		
		for (i = 0; i <= max; i++) {
			HashMap<String, String> postData = new HashMap<String, String>();
			date = new Date();
			time = String.valueOf(date.getTime());
			String account_name = String.format("%s-Account-Name", time);
			String description = String.format("%s\nTesting\n1\n2\n3...\nDONE>...", time);
			String email = String.format("%s@bar.com", time);
			postData.put("name", account_name);
			postData.put("description", description);
			postData.put("phone_office", "7078314197771");
			postData.put("email1", email);
			String postJson = json.toJson(postData);
			uri = String.format("%s/Accounts", testData.getValue("sugarinst"));
			
			try {
				client = new DefaultHttpClient();
				HttpPost post = new HttpPost(uri);
				HttpEntity entity = null;
				entity = new StringEntity(postJson, "application/json", "UTF-8");
				post.setEntity(entity);
				post.addHeader("OAuth Token", token);
				post.addHeader("User-Agent", "evilkook");
				HttpResponse response = client.execute(post);
				String tmp = TestUtils.bufferToString(response.getEntity());
				
				int status = response.getStatusLine().getStatusCode();
				if (status != 200) {
					System.out.printf("Error: Creating Account: '%s'\n", tmp);
				} else {
					AccountId id = json.fromJson(tmp, AccountId.class);
					result.add(id.id);
					System.out.printf("[%d]Created account: '%s'\n", i, id.id);
				}
			} catch (Exception exp) {
				exp.printStackTrace();
				result = null;
			}
		}
		return result;
	}
	
	public class UserId {
		private String token;
	}
	
	public class AccountId {
		private String id;
	}
	
}
