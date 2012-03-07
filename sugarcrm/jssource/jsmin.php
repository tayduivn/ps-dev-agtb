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

class EX_EOF extends Exception {}

class Tokenizer {
    function __construct($text, $quote_string = FALSE) {
        $this->text = preg_replace('/\r\n?/', "\n", $text);
        $this->pos = 0;
        $this->tokpos = 0;
        $this->line = 0;
        $this->tokline = 0;
        $this->col = 0;
        $this->tokcol = 0;
        $this->newline_before = false;
        $this->regex_allowed = false;
        $this->comments_before = array();
        $this->quote_string = $quote_string;

        $this->WHITESPACE_CHARS = array(" ", "\n", "\t", "\f", "\r", "\s", "\v");
        $this->KEYWORDS = array("break", "case", "catch", "const", "continue", "debugger", "default", "delete", "do", "else", "finally", "for", "function",
                "if", "in", "instanceof", "new", "return", "switch", "throw", "try", "typeof", "var", "void", "while", "with");
        $this->RESERVED_WORDS = array("abstract", "boolean","byte","char","class","double","enum","export","extends","final","float","goto","implements","import","int","interface","long","native",
                "package","private","protected","public","short","static","super","synchronized","throws","transient","volatile");
        $this->KEYWORDS_BEFORE_EXPRESSION = array("return", "new", "delete", "throw", "else", "case");
        $this->KEYWORDS_ATOM = array("false", "null", "true", "undefined");
        $this->OPERATORS = array("in", "instanceof","typeof","new","void","delete","++","--","+","-","!",
                "~","&","|","^","*","/","%",">>","<<",">>>","<",">","<=",">=","==","===","!=","!==",
                "?","=","+=","-=","/=","*=","%=",">>=","<<=",">>>=","|=","^=","&=","&&","||");
        $this->UNARY_POSTFIX = array("--", "++");
        $this->PUNC_CHARS = str_split("[]{}(),;:");
        $this->PUNC_BEFORE_EXPRESSION = str_split("[{(,.;:");
        $this->OPERATOR_CHARS = str_split("+-*&%=<>!?|~^");
    }

    function charAt($pos) {
        return substr($this->text, $pos, 1);
    }

    function peek() {
        return $this->charAt($this->pos);
    }

    function nextChar($signal_eof = null, $in_string = null) {
        $ch = $this->charAt($this->pos++);
        if($signal_eof && $ch === FALSE) {
            throw new EX_EOF;
        }
        if($ch == "\n") {
            $this->newline_before = $this->newline_before ? $this->newline_before : !$in_string;
            $this->line++;
            $this->col = 0;
        } else {
            $this->col++;
        }
        return $ch;
    }

    function eof() {
        return $this->peek() === FALSE;
    }

    function find($str, $signal_eof = FALSE) {
        $pos = strpos($this->text, $str, $this->pos);
        if($signal_eof && $pos === FALSE) {
            throw new EX_EOF;
        }
        return $pos;
    }

    function start_token() {
        $this->tokline = $this->line;
        $this->tokcol = $this->col;
        $this->tokpos = $this->pos;
    }

    function token($type, $value = null, $is_comment = FALSE) {
        if($type == "operator" && !in_array($value, $this->UNARY_POSTFIX)) {
            $this->regex_allowed = TRUE;
        } elseif ($type == "keyword" && in_array($value, $this->KEYWORDS_BEFORE_EXPRESSION)) {
            $this->regex_allowed = TRUE;
        } elseif ($type == "punc" && in_array($value, $this->PUNC_BEFORE_EXPRESSION)) {
            $this->regex_allowed = TRUE;
        } else {
            $this->regex_allowed = FALSE;
        }

        $ret = array(
            "type" => $type,
            "value" => $value,
            "line" => $this->tokline,
            "col" => $this->tokcol,
            "pos" => $this->tokpos,
            "endpos" => $this->pos,
            "nlb" => $this->newline_before,
        );
        if(!$is_comment) {
            $ret["comments_before"] = $this->comments_before;
            $this->comments_before = array();
        }
        $this->newline_before = FALSE;
        return $ret;
    }

    function skip_whitespace() {
        $ch = $this->peek();
        while((in_array($ch, $this->WHITESPACE_CHARS) || ord($ch) < 32) && $ch !== FALSE) {
            $this->nextChar();
            $ch = $this->peek();
        }
    }

    function read_while($pred) {
        $ret = '';
        $ch = $this->peek();
        $i = 0;
        while($ch !== FALSE && $pred($ch, $i++)) {
            $ret .= $this->nextChar();
            $ch = $this->peek();
        }
        return $ret;
    }

    function parse_error($err)  {
        throw new JS_Parse_Error($err, $this->tokline, $this->tokcol, $this->tokpos);
    }

    function read_num($prefix = "") {
        $has_e = $after_e = $has_x = FALSE;
        // Check for an initial dot.
        $has_dot = ($prefix == '.');
        $num = $this->read_while(function($ch, $i) use($has_e, $after_e, $has_x, $has_dot) {
            if($ch == 'x' || $ch == 'X') {
                // Check for hex number.
                if($has_x) {
                    // There cannot be more than one 'x' in a number.
                    return FALSE;
                }
                $has_x = TRUE;
                return $has_x;
            }

            if(!$has_x && ($ch == 'e' || $ch == 'E')) {
                // A hex number cannot have an exp value.
                if($has_e) {
                    // There cannot be more than one exp value in a number.
                    return FALSE;
                }
                // The next character is immediately after the exp indicator ('e').
                $has_e = $after_e = TRUE;
                return $has_e;
            }

            if($ch == '-') {
                if($after_e || ($i == 0 && !$prefix)) {
                    // Only allow a negative sign after an exp, or as the first character.
                    return TRUE;
                }
                return FALSE;
            }

            if($ch == '+') {
                // Only allow a positive sign after an exp.
                return $after_e;
            }
            // The next characters are not the one immediately after the exp indicator.
            $after_e = FALSE;

            if($ch == '.') {
                if(!$has_dot && !$has_x) {
                    // Only allow one dot in non-hex numbers.
                    $has_dot = TRUE;
                    return $has_dot;
                }
                return FALSE;
            }

            // We're good to determine this as a number as long as we've got an alphanumeric character.
            return is_alphanumeric_char($ch);
        });
        if($prefix) {
            $num = $prefix.$num;
        }
        $valid = parse_js_number($num);
        if(!is_nan($valid)) {
            return $this->token("num", $valid);
        } else {
            $this->parse_error("Invalid syntax: " . $num);
        }
    }

    function read_escaped_char($in_string = null) {
        $ch = $this->nextChar(TRUE, $in_string);
        switch ($ch) {
            case "n" : return "\n";
            case "r" : return "\r";
            case "t" : return "\t";
            case "b" : return "\b";
            case "v" : return "\u000b";
            case "f" : return "\f";
            case "0" : return "\0";
            case "x" : return chr($this->hex_bytes(2));
            case "u" : return chr($this->hex_bytes(4));
            case "\n": return "";
            default  : return $ch;
        }
    }

    function hex_bytes($n) {
        $num = 0;
        for(; $n > 0; --$n) {
            $digit = intval($this->nextChar(TRUE), 16);
            if(is_nan($digit)) {
                $this->parse_error("Invalid hex-char pattern in string.");
            }
            // Bitwise ops to get the right number;
            $num = ($num << 4) | $digit;
        }
        return $num;
    }

    function read_string($quote_string = FALSE) {
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->with_eof_error('Unterminated string constant', function() use ($ref, $quote_string) {
            $ret = '';
            $quote = $ref->nextChar();

            // End of file is handled through the exception, so we can use an infinite safely.
            while(TRUE) {
                $ch = $ref->nextChar(TRUE);
                if($ch == '\\') {
                    $octal_length = 0;
                    $first = null;
                    // Read any octal escape sequences we have.
                    $ch = $ref->read_while(function($ch) use($octal_length, $first) {
                        if ($ch >= "0" && $ch <= "7") {
                            if(is_null($first)) {
                                $first = $ch;
                                return ++$octal_length;
                            } elseif ($first <= "3" && $octal_length <= 2) {
                                return ++$octal_length;
                            } elseif ($first >= "4" && $octal_length <= 1) {
                                return ++$octal_length;
                            }
                            return FALSE;
                        }
                    });
                    if($octal_length > 0) {
                        $ch = chr(intval($ch, 8));
                    } else {
                        // It's just a usual escaped character.
                        $ch = $ref->read_escaped_char(TRUE);
                    }
                } elseif ($ch == $quote) {
                    // We found the matching quote.
                    break;
                }
                $ret .= $ch;
            }
            if($quote_string) {
                $ret = $quote.$ret.$quote;
            }
            return $ref->token("string", $ret);
        });
    }

    private function read_line_comment() {
        $this->nextChar();
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->with_eof_error('Unterminated single-line comment', function() use ($ref) {
            // We need to handle the special case where there is a comment at the end of the file without a newline character at the end. This is why we do not throw an exception at EOF, but just continue on as if everything's fine.
            $i = $ref->find("\n", FALSE);

            if($i === FALSE) {
                // Comment goes until the end of file.
                $ret = substr($ref->text, $ref->pos);
                $ref->pos = strlen($ref->text);
            } else {
                $ret = substr($ref->text, $ref->pos, $i - $ref->pos);
                $ref->pos = $i;
            }
            return $ref->token("comment1", $ret, TRUE);
        });
    }

    private function read_multiline_comment() {
        $this->nextChar();
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->with_eof_error('Unterminated multi-line comment', function () use ($ref) {
            $i = $ref->find("*/", TRUE);
            $text = substr($ref->text, $ref->pos, $i - $ref->pos);
            $ref->pos = $i+2;
            $ref->line += count(explode("\n", $text)) - 1;
            $ref->newline_before = strpos($text, "\n") !== FALSE;
            return $ref->token("comment2", $text, TRUE);
        });
    }

    function read_name() {
        $backslash = $escaped = FALSE;
        $name = "";

        while(!is_null($ch = $this->peek())) {
            if(!$backslash) {
                if($ch == "\\") {
                    $escaped = $backslash = TRUE;
                    $this->nextChar();
                } elseif (is_identifier_char($ch)) {
                    $name .= $this->nextChar();
                } else {
                    break;
                }
            } else {
                /*if($ch != "u") {
                    $this->parse_error("Expecting UnicodeEscapeSequence -- uXXXX");
                }*/
                $ch = $this->read_escaped_char();
                if (!is_identifier_char($ch)) {
                    $this->parse_error("Unicode char: ".$ch." is not valid in identifier.");
                    $name .= $ch;
                    $backslash = FALSE;
                }
            }
        }
        if(in_array($name, $this->KEYWORDS) && $escaped) {
            // TODO: Finish this up later when we have better unicode support.
        }
        return $name;
    }

    function read_regexp($regexp) {
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->with_eof_error('Unterminated regular expression', function ($regexp = "") use ($ref) {
            $prev_backslash = false;
            $ch = null;
            $in_class = false;

            while(($ch = $ref->nextChar(TRUE)) !== FALSE) {
                if ($prev_backslash) {
                    $regexp .= "\\" . $ch;
                    $prev_backslash = FALSE;
                } elseif ($ch == "[") {
                    $in_class = TRUE;
                    $regexp .= $ch;
                } elseif ($ch == "]" && $in_class) {
                    $in_class = FALSE;
                    $regexp .= $ch;
                } elseif ($ch == '/' && !$in_class) {
                    break;
                } elseif ($ch == '\\') {
                    $prev_backslash = TRUE;
                } else {
                    $regexp .= $ch;
                }
            }

            $mods = $ref->read_name();
            return $ref->token("regexp", array($regexp, $mods));
        });
    }

    function read_operator($prefix = "") {
        // Workaround until PHP 5.4.
        $ref = $this;
        $grow = function($op) use($ref, &$grow) {
            if(!$ref->peek()) {
                return $op;
            }
            $bigger = $op.$ref->peek();
            if(in_array($bigger, $ref->OPERATORS)) {
                $ref->nextChar();
                return $grow($bigger);
            } else {
                return $op;
            }
        };
        if(!$prefix) {
            $prefix = $this->nextChar();
        }
        return $this->token("operator", $grow($prefix));
    }

    private function handle_slash() {
        $this->nextChar();
        $regex_allowed = $this->regex_allowed;
        switch($this->peek()) {
            case "/":
                // Single line comment.
                array_push($this->comments_before, $this->read_line_comment());
                $this->regex_allowed = $regex_allowed;
                return $this->get_token();
            case "*":
                // Multi-line comment.
                array_push($this->comments_before, $this->read_multiline_comment());
                $this->regex_allowed = $regex_allowed;
                return $this->get_token();
        }

        if($this->regex_allowed) {
            return $this->read_regexp("");
        } else {
            return $this->read_operator("/");
        }
    }

    private function handle_dot() {
        $this->nextChar();
        return is_digit($this->peek()) ? $this->read_num(".") : $this->token("punc", ".");
    }

    function read_word() {
        $word = $this->read_name();
        if(!in_array($word, $this->KEYWORDS)) {
            return $this->token("name", $word);
        } else {
            if(in_array($word, $this->OPERATORS)) {
                return $this->token("operator", $word);
            } else {
                if(in_array($word, $this->KEYWORDS_ATOM)) {
                    return $this->token("atom", $word);
                } else {
                    return $this->token("keyword", $word);
                }
            }
        }
    }

    private function with_eof_error($message, $function) {
        try {
            return $function();
        } catch (Exception $e) {
            if(is_a($e, "EX_EOF")) {
                $this->parse_error($message);
            } else {
                throw new Exception($message);
            }
        }
    }

    function get_token($force_regexp = null) {
        if(!is_null($force_regexp)) {
            return $this->read_regexp($force_regexp);
        }

        $this->skip_whitespace();
        $this->start_token();
        $ch = $this->peek();

        if($ch === FALSE) {
            return $this->token("eof");
        }

        if(is_digit($ch)) {
            return $this->read_num();
        }

        if($ch == '"' || $ch == "'") {
            return $this->read_string($this->quote_string);
        }

        if(in_array($ch, $this->PUNC_CHARS)) {
            return $this->token("punc", $this->nextChar());
        }

        if($ch == ".") {
            return $this->handle_dot();
        }

        if($ch == "/") {
            return $this->handle_slash();
        }

        if (in_array($ch, $this->OPERATOR_CHARS)) {
            return $this->read_operator();
        }

        if($ch == "\\" || is_identifier_start($ch)) {
            return $this->read_word();
        }
        $this->parse_error("Unexpected character '" . $ch . "' (". ord($ch) . ")");

    }

    function get_token_context($nc) {
        $ret = $this;
        if($nc) {
            $ret = $nc;
        }
        return $ret;
    }

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
        $token = $tokenizer->get_token();
        $str = '';
        while($token["type"] != "eof") {
            $substr = $this->preprocess($token);
            $substr .= $token["value"];
            $substr .= $this->postprocess($token);

            $substr = str_replace('  ', ' ', $substr);
            $substr = str_replace(') ;', ');', $substr);
            $substr = str_replace(') .', ').', $substr);
            
            $str .= $substr;

            $token = $tokenizer->get_token();
        }
        return $str;
	}

    private function preprocess($token) {
        $ret = '';
        $SPACE_PUNC = str_split("([{");
        if ($token["type"] == "punc" && in_array($token['value'], $SPACE_PUNC)) {
            $ret = ' ';
        }
        return $ret;
    }

    private function postprocess($token) {
        $ret = '';
        $SPACE_PUNC = str_split(")]},;:");
        if($token["type"] == "keyword") {
            $ret = ' ';
        } elseif ($token["type"] == "punc" && in_array($token['value'], $SPACE_PUNC)) {
            $ret = ' ';
        }
        return $ret;
    }
}
