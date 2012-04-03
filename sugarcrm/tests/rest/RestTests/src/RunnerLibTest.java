import com.sugarcrm.restlib.SugarRest;


public class RunnerLibTest {

	/**
	 * @param args
	 */
	public static void main(String[] args) {
		// TODO Auto-generated method stub

		SugarRest rest = null;
		
		try {
			rest = new SugarRest("http://localhost:8888/sugar/ent/sugarcrm/rest");
			rest.setDebug(true);
			rest.login("admin", "admin", "text", null);
		} catch (Exception exp) {
			exp.printStackTrace();
		}
		
		
	}

}
