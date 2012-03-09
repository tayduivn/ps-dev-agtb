<?php

class JS_Parse_Error extends Exception {
    function __construct($message, $line, $col, $pos) {
        $this->message = $message;
        $this->line = $line+1;
        $this->col = $col+1;
        $this->pos = $pos+1;
    }

    function __toString() {
        return $this->message . " (line: " . $this->line . ", col: " . $this->col . ", pos: " . $this->pos . ")" . "\n\n";
    }
}

function js_error($message, $line, $col, $pos) {
    throw new JS_Parse_Error($message, $line, $col, $pos);
};

function is_digit($ch) {
    $code = ord($ch);
    return ($code >= 48 && $code <= 57);
}

function is_letter($ch) {
    $code = ord($ch);
    if($code >= 65 && $code <= 90) {
        // Capital letter.
        return true;
    } elseif ($code >= 97 && $code <= 122) {
        // Lowercase letter.
        return true;
    }
    return false;
}

function is_alphanumeric_char($ch) {
    return is_digit($ch) || is_letter($ch);
}

function is_identifier_start($ch) {
    return $ch == "$" || $ch == "_" || is_letter($ch);
}

function is_identifier_char($ch) {
    return (is_identifier_start($ch) || is_digit($ch));
}

function parse_js_number($num) {
    $RE_HEX_NUMBER = "/^0x[0-9a-f]+$/i";
    $RE_OCT_NUMBER = "/^0[0-7]+$/";
    $RE_DEC_NUMBER = "/^\d*\.?\d*(?:e[+-]?\d*(?:\d\.?|\.?\d)\d*)?$/i";

    if(preg_match($RE_HEX_NUMBER, $num)) {
        return intval(substr($num, 2), 16);
    } elseif (preg_match($RE_OCT_NUMBER, $num)) {
        return intval(substr($num, 1), 8);
    } elseif (preg_match($RE_DEC_NUMBER, $num)) {
        return floatval($num);
    }
}

function is_token($token, $type, $val) {
        return $token->type == $type && ($val == null || $token->value == $val);
}

class JSMin {
    /**
     * Calls the SugarMin minify function.
     *
     * @param string $js Javascript to be minified
     * @return string Minified javascript
     */
    public static function minify($js, $filename = '') {
        return SugarMin::minify($js);
    }
}

class SugarMin {

    /**
     * jsParser will take javascript source code and minify it.
     *
     * Note: There is a lot of redundant code since both passes
     * operate similarly but with slight differences. It will probably
     * be a good idea to refactor the code at a later point when it is stable.
     *
     * JSParser will perform 3 passes on the code. Pass 1 takes care of single
     * line and mult-line comments. Pass 2 performs some sanitation on each of the lines
     * and pass 3 works on stripping out unnecessary spaces.
     *
     * @param string $js
     * @param string $currentOptions
     * @return void
     */
    private function __construct($text, $compression) {
        $this->text = trim($text);
        $this->compression = $compression;

        // Check for BOM.
        if(substr($this->text, 0, 3) == pack("CCC", 0xEF, 0xBB, 0xBF)) {
            $this->text = substr($this->text, 3);
        }
    }

    /**
     * Entry point function to minify javascript.
     *
     * @param string $js Javascript source code as a string.
     * @param string $compression Compression option. {light, deep}.
     * @return string $output Output javascript code as a string.
     */
    static public function minify($js, $compression = 'light') {
        try {
            $me = new SugarMin($js, $compression);
            $output = $me->jsParser();

            return $output;
        } catch (Exception $e) {
            // Exception handling is left up to the implementer.
            throw $e;
        }
    }

    protected function jsParser() {
        $tokenizer = new Tokenizer($this->text, TRUE);
        $prev_token = null;
        $token = $tokenizer->get_token();
        $str = "\n";
        while($token["type"] != "eof") {
            if($token["type"] == "regexp") {
                $token["value"] = '/'.$token["value"][0].'/'.$token["value"][1];
            }

            $substr = $this->preprocess($token, $prev_token);
            $substr .= $token["value"];
            $substr .= $this->postprocess($token);

            $str .= $substr;

            $prev_token = $token;
            $token = $tokenizer->get_token();
        }

        $str = str_replace(', ', ',', $str);
        $str = str_replace(' [', '[', $str);
        $str = str_replace(' (', '(', $str);
        $str = str_replace('] ', ']', $str);
        $str = str_replace(' ;', ';', $str);
        $str = str_replace(') .', ').', $str);
        $str = str_replace('elseif', 'else if', $str);
        
        return $str;
	}

    private function preprocess($token, $prev_token = null) {
        $ret = '';
        $SPACE_PUNC = str_split("([{");
        $SPACE_BEFORE_KEYWORDS = array("in", "instanceof");

        if(!is_null($prev_token) && $prev_token["type"] == "punc" && $prev_token["value"] == "}" && $prev_token['block_type'] == 'function' && $token["type"] == "name") {
            $ret = ';';
        } elseif(($token["type"] == "keyword" || $token["type"] == "operator") && in_array($token["value"], $SPACE_BEFORE_KEYWORDS)) {
            $ret = ' ';
        }

        return $ret;
    }

    private function postprocess($token) {
        $ret = '';
        $SPACE_PUNC = str_split(")]},;:");
        $SPACE_AFTER_KEYWORDS = array("case", "catch", "function", "in", "instanceof", "new", "return", "throw", "typeof", "var");

        if(($token["type"] == "keyword" || $token["type"] == "operator") && in_array($token["value"], $SPACE_AFTER_KEYWORDS)) {
            $ret = ' ';
        }
        return $ret;
    }
}
