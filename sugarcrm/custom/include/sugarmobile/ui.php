<?php
class SUI_page {
	var $content;
	var $title;

	function SUI_page($the_title,$bgcolor="#cccccc") {
		$this->content = "<html><head><title>$the_title</title></head>";
		$this->content .= '<body bgcolor="'.$bgcolor.'">';
	}

	function render() {
		echo $this->content;
		echo '</html>';
	}

	function add_newline() {
		$this->content .= '<br />';
	}

	function add_link($link_text, $url, $br=1) {
		$this->content .= "<a href=\"$url\">" . $link_text . "</a>";
		if($br) { $this->content .= '<br>'; }
	}

	function add_text($text,$br=1) {
		$this->content .= $text;
		if($br) { $this->content .= '<br>'; }
	}
	
	function add_phone($link_text, $br=1) {
		$condensed = ereg_replace("[^+0-9]", "", $link_text);
		$this->content .= "<a href=\"wtai://wp/mc;$condensed\">" . $link_text . "</a>";
		if($br) { $this->content .= '<br>'; }
	}

}


class SUI_form {
	var $action;
	var $content;


	function SUI_form($action) {
		$this->action = $action;
	}

	function render($br=0) {
		echo '<form action="'.$this->action.'">';
		echo $this->content;
		echo '</form>';
		if ($br) { echo '<br />'; }
	}

	function add_newline() {
		$this->content .= '<br />';
	}

	function add_hidden($name, $value) {
		$this->content .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
	}
	
	function add_input($name, $value, $label,$br=1) {
		$this->content .= $label . '<input name="'.$name.'" value="'.$value.'">';
		if ($br) { $this->content .=  '<br />'; }
	}



	function add_password($name, $value, $label,$br=1) {
		$this->content .= $label . '<input name="'.$name.'" value="'.$value.'" type="password">';
		if ($br) { $this->content .=  '<br />'; }
	}

	function add_bool($name, $value, $label,$br=1) {
		$this->content .= $label . '<input';
		if ($value == 1){
			$this->content .= ' checked';
		}

		$this->content .= ' type="checkbox" name="'.$name.'">';
		if ($br) {
			$this->content .=  '<br />';
		}
	}


	function add_button($label) {
		$this->content .= '<input value="'.$label.'" type="submit"></input>';
	}
	
	function add_text($text, $label,$br=1) {
		$this->content .= $label . $text;
		if ($br) { $this->content .=  '<br />'; }
	}

	function add_select($list = array(), $name, $default = '',$label='',$br=1) {
		$this->content .= $label . '<select name="'.$name.'">';
		foreach ($list as $key=>$value) {
			if ($value == $default) {
				$this->content .= '<option value="'.$key.'" selected>'.$value.'</option>';
			} else {
				$this->content .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}
		$this->content .= '</select>';
		
		if ($br) { $this->content .=  '<br />'; }
	}

}
	
?>
