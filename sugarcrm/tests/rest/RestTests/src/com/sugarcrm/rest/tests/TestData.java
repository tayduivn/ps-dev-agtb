package com.sugarcrm.rest.tests;

import java.util.HashMap;

public class TestData {

	public String BaseURL = "http://localhost:8888/sugar/ent/sugarcrm/rest";
	public String UserName = "admin";
	public String UserPass = "admin";
	public HashMap<String, String> data = null;
	
	public TestData() {
		String tmp = "";
		
		this.data = new HashMap<String, String>();
		
		tmp = System.getProperty("sugar-inst");
		if (tmp != null) {
			data.put("sugarinst", tmp);
		} else {
			data.put("sugarinst", this.BaseURL);
		}
		
		tmp = System.getProperty("sugar-username");
		if (tmp != null) {
			data.put("sugaruser", tmp);
		} else {
			data.put("sugaruser", this.UserName);
		}
		
		tmp = System.getProperty("sugar-password");
		if (tmp != null) {
			data.put("sugarpass", tmp);
		} else {
			data.put("sugarpass", this.UserName);
		}
	}
	
	public String getValue(String key) {
		return this.data.get(key);
	}
	
}
