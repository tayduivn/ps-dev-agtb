package com.sugarcrm.rest.tests;

import java.io.BufferedReader;
import java.io.InputStreamReader;
import org.apache.http.HttpEntity;

public class TestUtils {

	/**
	 * This function takes an {@link HttpEntity} and converts its payload into a {@link String}.
	 * 
	 * @param entity
	 * @return
	 */
	public static String bufferToString(HttpEntity entity) {
		String buffer = "";
		String tmp = "";
		InputStreamReader in = null;
		BufferedReader reader = null;
		
		try {
			in = new InputStreamReader(entity.getContent());
			reader = new BufferedReader(in);

			while ((tmp = reader.readLine()) != null) {
				buffer = buffer + tmp;
			}
		} catch (Exception exp) {
			exp.printStackTrace();
			buffer = null;
		}
		
		return buffer;
	}
}
