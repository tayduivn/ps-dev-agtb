package com.sugarcrm.rest.tests;

import junit.framework.TestSuite;
import org.junit.runner.RunWith;
import org.junit.runners.Suite;
import org.junit.runners.Suite.SuiteClasses;

@RunWith(Suite.class)
@SuiteClasses({ 
	TestAccountCreate.class, 
	TestAccountDelete.class,
	TestAccountObjects.class, 
	TestAccountUpdate.class, 
	TestServerInfo.class,
	TestAccountContactsRelate.class})

public class AllTests extends TestSuite {
	
}
