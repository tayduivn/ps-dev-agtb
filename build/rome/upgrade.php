<?php

class RoadToRome{
/**************CODE FOR UPGRADING COMMENTS FROM OLD FORMAT***********************/
function upgrade($path){
	if(empty($this->startPath))$this->startPath = $path;
	$d = dir($path);
	//don't convert build directory
	if(file_exists($path . '/Rome.php'))return true;
	while($e = $d->read()){
		if(substr($e, 0, 1) == '.')continue;
		$next = $path . '/' . $e;
		if(is_dir($next)){
            $this->upgrade($next);
		}else{
			$this->upgradeComments($next);
		}
	}
	if($path == $this->startPath)echo 'DONE' . "\n";
    return true;
}

function upgradeComments ($path){
	    $line_array = array();
	    $distro_flag = '';
        $this->file = $path;
		$fp = fopen($path, 'r');
        $tmp_out = array();
        $out='';
        $ii=0;
        $distro_option ='';
        $exp_flag =0;

        while($line = fgets($fp)){
        	$line_array[$ii]=$line;
        	$ii++;
        }
        // first loop to upgrade DISTRO tags
        $jj=0;
        for ($ii=0; $ii<=count($line_array); $ii++){
        	if (empty($line_array[$ii])) break;
        	$line = $line_array[$ii];
        	if(!empty($line_array[$ii+1]) && !empty($line_array[$ii+2]) ){
        		$next_line=$line_array[$ii+1];
        		$next2_line=$line_array[$ii+2];
        		if(substr_count($next_line, 'DISTRO')!= 0){
        			if(substr_count($line, 'SUGARCRM')== 0 ) {
        				if(substr_count($line, 'DISTRO')!= 0){
        					// current line has distro tag as well
        					$tmp_out[$jj]= $this->pregDistro($next_line, 2);
                			$jj++;
        				}else{
        					$tmp_out[$jj] = $line;
        					$jj++;
        				}
        				// could be one line DISTRO or end distro with end sugarcrm
                		if (substr_count($next2_line, 'SUGARCRM')== 0){
                			// one line distro only
                			$tmp_out[$jj]= $this->pregDistro($next_line, 2);
                			$ii++;

                		}else{
                			// end distro with end sugarcrm
                			$distro_option = $this->pregDistro($next_line, 0);
                			$s = $this->upgradeComment($next2_line);
                			$s = str_replace("ONLY", $distro_option, $s);
                			if($exp_flag !=0) {
                				// end distro and end sugarcrm with begins
                				$tmp_out[$jj] = str_replace("flav=com  && dep=od", "flav=exp" , $s);
                				$exp_flag =0;
                			}else {
                				$tmp_out[$jj] = $s;
                			}
                			$ii += 2;
                		}
           			}else{
           				// could be begin sugarcrm and DISTRO or begin sugarcrm, distro with end sugarcrm
                		if (substr_count($next2_line, 'SUGARCRM')!= 0){
                			// begin sugarcrm, distro with end sugarcrm
                			$tmp_out[$jj]= $this->pregDistro($next_line, 1);
                			$ii = $ii + 2;

                		}else{
                			// begin sugarcrm with begin distro
                			$distro_option = $this->pregDistro($next_line, 0);
                			$s = $this->upgradeComment($line);
                			$s = str_replace("ONLY", $distro_option, $s);
                			if (substr_count($s, 'flav=com  && dep=od')!= 0){
                				$tmp_out[$jj] = str_replace("flav=com  && dep=od", "flav=exp" , $s);
                				$exp_flag = 1;
                			}else {
                				$tmp_out[$jj] = $s;
                			}
                			$ii++;

                		}

           			}
        		}else{
        			    // without distro, regular commands
        			    // look at the current line as well.
        				if(substr_count($line, 'DISTRO')!= 0){
        					// current line has distro tag as well
        					$tmp_out[$jj]= $this->pregDistro($line, 2);
        				}else{
        					$tmp_out[$jj] = $line;
        				}
        		}
        	}else{
        		$tmp_out[$jj] = $line;
        	}
        	$jj++;
       		//if(substr_count($line, 'DISTRO')== 0 ) {
            //    	$tmp_out[$ii]= $line;
           	//}else{
            //   	$tmp_out[$ii]= $this->upgradeDistro($line_array, $ii);
           //	}

        }
        // loop out again to upgrade "SUGARCRM" comments
        $line_array = $tmp_out;
        for ($ii=0; $ii<=count($line_array); $ii++){
        	if (empty($line_array[$ii])) break;
        	$line = $line_array[$ii];
       		if(substr_count($line, 'SUGARCRM') == 0 ){
                	$out .= $line;
           	}else{
                	$out .= $this->upgradeComment($line);
           	}
        }
        file_put_contents($path, $out);
}

function upgradeComment($line){
	$s='';
	$needle = array ('flav','reg','dep','sub','pro');
	$count = 0;
    foreach ($needle as $substring) {
          if(substr_count( $line, $substring)>0) return $line;;
     }

	 // BEGIN SUGARCRM PRO ONLY
     preg_match('/(.*)\/\/\s*(BEGIN|END|FILE)\s*SUGARCRM\s*(.*) ONLY\s*(.*)/i', $line, $match);
     if (!empty($match[1])){
     	if(empty($match[2])){
     		// BEGIN EXCLUDE SUGARCRM DCE ONLY
			preg_match('/(.*)\/\/\s*(BEGIN|END|FILE)\s*EXCLUDE\s*SUGARCRM\s*(.*) ONLY\s*(.*)/i', $line, $match);
			if(empty($match[2]))return $line;
			if (!empty($match[4])) $s= $match[4];
			return $match[1] . '//' . $match[2] . ' SUGARCRM flav!=' . strtolower($match[3]) . " ONLY". " $s\n";
	 	}
	 	if (!empty($match[4])) $s= $match[4];
	 	return $match[1] . '//' . $match[2] . ' SUGARCRM flav=' . strtolower($match[3]) . " ONLY". " $s\n";
     }else{
     	// <!-- BEGIN SUGARCRM PRO ONLY
     	preg_match('/(.*)\s*(BEGIN|END|FILE)\s*SUGARCRM\s*(.*) ONLY\s*(.*)/i', $line, $match);
     	if(empty($match[2])){
     		// <!-- BEGIN EXCLUDE SUGARCRM DCE ONLY
			preg_match('/(.*)\s*(BEGIN|END|FILE)\s*EXCLUDE\s*SUGARCRM\s*(.*) ONLY\s*(.*)/i', $line, $match);
			if(empty($match[2]))return $line;
			if (!empty($match[4])) $s= $match[4];
			if(substr_count($match[1], '//') == 0 ){
				return $match[1] . '//' . $match[2] . ' SUGARCRM flav!=' . strtolower($match[3]) . " ONLY". " $s\n";
			}else{
				return $match[1] . $match[2] . ' SUGARCRM flav!=' . strtolower($match[3]) . " ONLY". " $s\n";
			}
	 	}else {
	 		if (!empty($match[4])) $s= $match[4];
	 		if(substr_count($match[1], '//') == 0 ){
				return $match[1] . '//' . $match[2] . ' SUGARCRM flav=' . strtolower($match[3]) . " ONLY". " $s\n";
			}else{
				return $match[1]  . $match[2] . ' SUGARCRM flav=' . strtolower($match[3]) . " ONLY". " $s\n";
			}
	 	}
     }
}
function pregDistro($line, $case){
	 // BEGIN|FILE|END DISTRO ONLY
	 $match = array();
     preg_match('/(.*)\/\/\s*(BEGIN|END|FILE)\s*DISTRO\s*(.*) ONLY/i', $line, $match);

     switch( $case ){
     	case "1":
     		if(!empty($match[3])){
     			if($match[3] == 'DEPLOY=OD'){
     				return $match[1] . '//' . $match[2] . " SUGARCRM lic=sub ONLY\n";
     			}
     		}
     	case "2":
     		if(!empty($match[3])){
     			$s = $match[1] . '//' . $match[2] . " SUGARCRM ";
     			preg_match('/(.*)\s*DEPLOY=\s*(.*)/i', $match[3], $option_match);
     			if (!empty($option_match[2])) $s =$s . "dep=" . strtolower($option_match[2]);
     			preg_match('/(.*)\s*REGION=\s*(.*)DEPLOY=\s*(.*)/i', $match[3], $option_match);
     			if (!empty($option_match[2])) $s =$s . " && reg=" . strtolower($option_match[2]);
     			return $s . " ONLY\n";
     		}
     	default:
     		if(!empty($match[3])){
     			$s='';
     			preg_match('/(.*)\s*DEPLOY=\s*(.*)/i', $match[3], $option_match);
     			if (!empty($option_match[2])) $s =$s . " && dep=" . strtolower($option_match[2]);
     			preg_match('/(.*)\s*REGION=\s*(.*)DEPLOY=\s*(.*)/i', $match[3], $option_match);
     			if (!empty($option_match[2])) $s =$s . " && reg=" . strtolower($option_match[2]);
     			return $s . " ONLY\n";
     		}
     }
     return $match[1] . '//' . $match[2] . " SUGARCRM flav=exp ONLY\n";
}
/**************END FOR UPGRADING COMMENTS FROM OLD FORMAT***********************/

}
$config = getConfig();
$rr = new RoadToRome();
$rr->upgrade($config['base_dir']);

function getConfig(){
	include('config/runtime.config.php');
	if(!isset($config))$config = array();
	/**
	 * ADD LOGIC HERE FOR COMMAND LINE ARGUMENTS
	 */
	return $config;

}