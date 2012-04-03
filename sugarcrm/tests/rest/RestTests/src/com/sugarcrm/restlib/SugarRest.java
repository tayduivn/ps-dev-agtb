package com.sugarcrm.restlib;

import java.text.SimpleDateFormat;
import java.util.Date;
import org.apache.http.HttpEntity;
import org.apache.http.client.methods.HttpPost;
import org.apache.http.entity.StringEntity;
import org.apache.http.impl.client.DefaultHttpClient;
import org.apache.http.protocol.BasicHttpContext;
import com.google.gson.Gson;

public class SugarRest {

	private String URL = null;
	private boolean debug = false;
	
	public SugarRest(String url) {
		this.URL = url;
	}
	
	/**
	 * 
	 * @param username
	 * @param password
	 * @param clientInfo
	 * @return
	 */
	public String login(String username, String password, String type, SugarLoginData clientInfo) {
		String token = null;
		HttpPost post = null;
		Gson json = null;
		String tmp = "";
		String jsonStr = "";
		DefaultHttpClient client = null;
		String uri = String.format("%s/login", this.URL);
		SugarLoginData data = new SugarLoginData();
		HttpEntity entity;
		BasicHttpContext context = null;
		data.put("username", username);
		data.put("password", password);
		data.put("type", type);
		data.put("clientinfo", clientInfo);
		
		try {
			json = new Gson();
			jsonStr = json.toJson(data);
			tmp = String.format("JSON Login Paylod: '%s'", jsonStr);
			PrintDebug(tmp);
			post = new HttpPost(uri);
			entity = new StringEntity(jsonStr, "application/json", "UTF-8");
			post.setEntity(entity);
			client = new DefaultHttpClient();
			context = new BasicHttpContext();
			client.execute(post, context);
			
			
		} catch (Exception exp) {
			exp.printStackTrace();
			token = null;
		}
		
		
		return token;
	}
	
	public void setDebug(boolean debug) {
		this.debug = debug;
	}
	
	private void PrintDebug(String msg) {
		if (this.debug) {
			Date now = new Date();
			SimpleDateFormat dateformat = new SimpleDateFormat("MM/dd/yyyy H:mm::ss.SSS");
			System.out.printf("[%s]: %s\n",dateformat.format(now), msg);
		}
	}
	
}
