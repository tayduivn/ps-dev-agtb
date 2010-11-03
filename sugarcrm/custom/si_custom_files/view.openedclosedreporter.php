<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
                
class OpenedClosedReporterView extends SugarView 
{	
    /**
     * Constructor
     */
 	public function __construct()
    {
 		parent::SugarView();
    }
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {
		echo "<p id=moduletitle><font size=3>".$this->module."</font></p>";
		if(empty($_REQUEST['start_date'])){
			$user_date_format = '(yyyy-mm-dd)';
			$calendar_date_format = '%Y-%m-%d';
			
$output =<<<EOQ
            <form name=BugsOpenClosedForm method=post action="{$_SERVER['REQUEST_URI']}">
            <table border="0" cellpadding="0" cellspacing="0" width="50%">
                <tr>
                <td width="50%" class="tabDetailViewDL">
                Please select the start date:
                </td>
                <td width="50%" class="tabDetailViewDF">
                <span sugar='slot2'><input name='start_date' onblur="parseDate(this, '$calendar_date_format');" id='startdate_jscal_field' type="text" tabindex='2' size='11' maxlength='10' value=""> <img src="themes/default/images/jscalendar.gif"  id="startdate_jscal_trigger" align="absmiddle"> <span class="dateFormat">$user_date_format</span></span sugar='slot'>
                </td>
                </tr>
                <tr>
                <td width="50%" class="tabDetailViewDL">
                Please select the end date:
                </td>
                <td width="50%" class="tabDetailViewDF">
                <span sugar='slot3'><input name='end_date' onblur="parseDate(this, '$calendar_date_format');" id='enddate_jscal_field' type="text" tabindex='2' size='11' maxlength='10' value=""> <img src="themes/default/images/jscalendar.gif"  id="enddate_jscal_trigger" align="absmiddle"> <span class="dateFormat">$user_date_format</span></span sugar='slot'>
                </td>
                </tr>
            </table>
            <BR>
            <input type=submit value=Submit>
            </form>
EOQ;
			echo $output;
			?>
            <script type="text/javascript">
            Calendar.setup ({
                inputField : "startdate_jscal_field", ifFormat : "<?php echo $calendar_date_format; ?>", showsTime : false, button : "startdate_jscal_trigger", singleClick : true, step : 1
            });
            </script>
            <script type="text/javascript">
            Calendar.setup ({
                inputField : "enddate_jscal_field", ifFormat : "<?php echo $calendar_date_format; ?>", showsTime : false, button : "enddate_jscal_trigger", singleClick : true, step : 1
            });
            </script>
            <?php
		}
		else{
			require('custom/si_custom_files/meta/bugsAndITRMeta.php');
			$search =  array('[module_display]', '[table_name]', '[start_date]', '[end_date]', '[closed_statuses]', '[status_field]');
			$replace = array($this->module, $this->bean->table_name, $_REQUEST['start_date'], $_REQUEST['end_date'], $this->closed_statuses, $this->status_field);

			$smarty_meta = array();
			foreach($bugsAndITRMeta as $index => $meta){
				$meta['comment'] = str_replace($search, $replace, $meta['comment']);
				$meta['query'] = str_replace($search, $replace, $meta['query']);
				$res = $GLOBALS['db']->query($meta['query']);
				$count = $GLOBALS['db']->getRowCount($res);
				// echo $meta['query']."<BR>";
				$meta['count'] = $count;
				unset($meta['query']);
				$smarty_meta[] = $meta;
			}
			
			if(isset($this->start_date_result)){
				$smarty_meta[] = array(
					'comment' => "Total bugs open at the beginning of this timeframe",
					'count' => $this->start_date_result,
				);
			}
			if(isset($this->end_date_result)){
				$smarty_meta[] = array(
					'comment' => "Total bugs open at the end of this timeframe",
					'count' => $this->end_date_result,
				);
			}
			$this->ss->assign('START_DATE', $_REQUEST['start_date']);
			$this->ss->assign('END_DATE', $_REQUEST['end_date']);
			$this->ss->assign('meta', $smarty_meta);
			$this->ss->display('custom/si_custom_files/meta/bugsAndITR.tpl');
		}
    }
}
?>
