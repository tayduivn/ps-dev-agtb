<?php

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

/**
 * SugarMin is a Javascript minifier with two levels of compression. The default compression
 * is set to light. Light compression will preserve some line breaks that may interfere with
 * operation of the script. Deep compression will remove all the line breaks, but before using
 * deep compression make sure the script has passed JSLint.
 */
class SugarMin {
    protected $noSpaceChars = array('\\', '$', '_', '/');
    protected $postNewLineSafeChars = array('\\', '$', '_', '{', '[', '(', '+', '-');
    protected $preNewLineSafeChars = array('\\', '$', '_', '}', ']', ')', '+', '-', '"', "'");
    protected $regexChars = array('(', ',',  '=', ':', '[', '!', '&', '|', '?', '{', '}', ';');
    protected $compression;
    protected $whitespaceChars = array(" ", "\n", "\r", "\t", "\f");

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
        $this->text = $text;
        $this->compression = $compression;
        $this->pos = 0;
        $this->line = 0;
        $this->col = 0;
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

    private function replace_newlines() {
        // We first perform a few operations to simplify our
        // minification process. We convert all carriage returns
        // into line breaks.
        $this->text = str_replace("\r\n", "\n", $this->text);
        $this->text = str_replace("\r", "\n", $this->text);

        // Then, we find all extra new line characters and remove those.
        $this->text = preg_replace("/\n+/","\n", $this->text);
    }

    function find($str, $signal_eof = FALSE) {
        $pos = strpos($this->text, $str);
        if($signal_eof && $pos === FALSE) {
            throw new Exception;
        }
        return $pos;
    }

    private function tryEOF($message, $function) {
        try {
            return $function();
        } catch (Exception $e) {
            throw new Exception($message);
        }
    }

    private function read_line_comment() {
        $this->nextChar();
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->tryEOF('Unterminated single-line comment.', function() use ($ref) {
            $i = $ref->find("\n", TRUE);
            if($i === FALSE) {
                // Comment goes until the end of file.
                $ret = substr($ref->text, $ref->pos);
                $ref->pos = strlen($ref->text);
            } else {
                $ret = substr($ref->text, $ref->pos, $i-$ref->pos);
            }
            return $ret;
        });
    }

    private function read_multiline_comment() {
        $this->nextChar();
        // The following line is a hack until PHP 5.4.
        $ref = $this;
        return $this->tryEOF('Unterminated multi-line comment.', function () use ($ref) {
            $i = $ref->find("*/", TRUE);
            $text = substr($ref->text, $ref->pos, $i-$ref->pos);
            $ref->nextChar(); $ref->nextChar();
            $ref->line += count(explode("\n", $text)) - 1;
            return $text;
        });
    }

    private function handle_slash(&$str) {
        $this->nextChar();
        switch($this->peek()) {
            case "/":
                // Single line comment.
                $comment = '//'.$this->read_line_comment();
                echo "Got comment: ".$comment."\n---\n";
                break;
            case "*":
                // Multi-line comment.
                $comment = '/*'.$this->read_multiline_comment().'*/';
                echo "Got multiline comment: ".$comment."\n---\n";
                break;
            default:
                // Looks like a regex, smells like a regex. Also could be a division operator.
                // TODO: need to catch the division operator.

                // The following line is a hack until PHP 5.4.
                $ref = $this;
                $str .= $this->tryEOF('Unterminated regular expression.', function ($regex = "") use ($ref) {
                    $prev_backslash = false;
                    $ch = null;
                    $in_class = false;
                    
                    while($ch = $ref->nextChar(TRUE)) {
                        if ($prev_backslash) {
                            $regex .= "\\" + $ch;
                            $prev_backslash = FALSE;
                        } elseif ($ch == "[") {
                            $in_class = TRUE;
                            $regex .= $ch;
                        } elseif ($in_class && $ch == "]") {
                            $in_class = FALSE;
                            $regex .= $ch;
                        } elseif ($ch == '/' && !$in_class) {
                            break;
                        } elseif ($ch == '\\') {
                            $prev_backslash = TRUE;
                        } else {
                            $regex .= $ch;
                        }
                    }

                    // TODO: check if we need the read_name() method.
                    return $regex;
                });
                break;
        }
    }

    function peek() {
        return $this->charAt($this->pos);
    }

    function nextChar($signal_eof = FALSE) {
        $ch = $this->charAt($this->pos++);
        if($signal_eof && !$ch) {
            throw new Exception;
        }
        if($ch == "\n") {
            $this->line++;
            $this->col = 0;
        } else {
            $this->col++;
        }
        return $ch;
    }

    function isEOF() {
        return $this->pos >= strlen($this->text);
    }

    function charAt($pos) {
        return substr($this->text, $pos, 1);
    }

    protected function jsParser() {
        $this->replace_newlines();
        

        $stripped_js = '';

        // Pass 1, strip out single line and multi-line comments.
        while(!$this->isEOF()) {
            $char = $this->peek();
            switch ($char) {
                case "\\": // If escape character
                    $next = $this->nextChar();
                    $stripped_js .= $char.$next;
                    break;
                case '"': // If string literal
                case "'":
                    $literal = $delimiter = $char;

                    while(!$this->isEOF()) {
                        $char = $this->nextChar();
                        $literal .= $char;
                        if($char == "\\") {
                            $literal .= $this->nextChar();
                            // Don't match the delimiter if it's escaped.
                            continue;
                        }

                        if ($char == $delimiter) {
                            // Found the closing delimiter.
                            break;
                        }
                    }

                    $stripped_js .= $literal;
                    break;
                case "/": // If comment or regex
                    $this->handle_slash($stripped_js);
                default:
                    $stripped_js .= $char;
                    break;
            }
            $this->nextChar();
        }

        // Split our string up into an array and iterate over each line
        // to do processing.
        $input = explode("\n", $stripped_js);
        $primedInput = array();

        // Pass 2, remove space and tabs from each line.
        for ($index = 0; $index < count($input); $index++) {
            $line = $input[$index];

            $line = trim($line, " \t");

            // If the line is empty, ignore it.
            if (strlen($line) == 0) {
                continue;
            }

            $primedInput[] = $line;
        }

        $input = $primedInput;
        $output = '';

        // Pass 3, remove extra spaces
        for ($index = 0; $index < count($input); $index++) {
            $line = $input[$index];
            $newLine = '';
            $len = strlen($line);

            $nextLine = ($index < count($input) -1 ) ? $input[$index + 1] : '';

            $lastChar = ($len > 0) ? $line[$len - 1] : $line[0];
            $nextChar = ($nextLine) ? $nextLine[0] : null;

            // Iterate through the string one character at a time.
            for ($i = 0; $i < $len; $i++) {
                switch($line[$i]) {
                    case "\\":
                        $newLine .= $line[$i].$line[$i + 1];
                        $i++;
                        break;
                    case '/':
                        // Check if regular expression
                        if (strlen($newLine) > 0 && in_array($newLine[strlen($newLine) - 1], $this->regexChars)) {
                            $nesting = 0;
                            $newLine .= $line[$i];

                            for ($j = $i + 1; $j < $len; $j++) {
                                if ($line[$j] == "\\") {
                                    $newLine .= $line[$j].$line[$j + 1];
                                    $j++;
                                    continue;
                                }

                                if ($line[$j] == '[') {
                                    $nesting++;
                                } else if ($line[$j] == ']') {
                                    $nesting--;
                                }

                                $newLine .= $line[$j];
                                if ($line[$j] == '/' && $nesting == 0 && $newLine[strlen($newLine) - 1] != "\\") {
                                    break;
                                }
                            }
                            $i = $j;
                        } else {
                            $newLine .= $line[$i];
                        }
                        break;
                    // String literals shall be transcribed as is.
                    case '"':
                    case "'":
                        $literal = $delimiter = $line[$i];

                        for ($j = $i + 1; $j < strlen($line); $j++) {
                            $literal .= $line[$j];

                            if ($line[$j] == "\\") {
                                $literal .= $line[$j + 1];
                                $j++;
                                continue;
                            }

                            if ($line[$j] == $delimiter) {
                                break;
                            }

                            if ($line[$j] == "\n") {

                            }
                        }

                        $i = $j;
                        $newLine .= $literal;
                        break;
                    // Tabs must be replaced with spaces and then re-evaluated to see if the space is necessary.
                    case "\t":
                        $line[$i] = " ";
                        $i--;
                        break;
                    case ' ':
                        if ( !(
                                (ctype_alnum($line[$i - 1]) || in_array($line[$i - 1], $this->noSpaceChars))
                                &&
                                (in_array($line[$i+1], $this->noSpaceChars) || ctype_alnum($line[$i+1]))
                            )) {
                            // Omit space;
                            break;
                        }
                    default:
                        $newLine .= $line[$i];
                        break;
                }
            }

            if ((ctype_alnum($lastChar) || in_array($lastChar, $this->preNewLineSafeChars)) && ((in_array($nextChar, $this->postNewLineSafeChars) || ctype_alnum($nextChar)))) {
                $newLine .= "\n";
            }

            $output .= $newLine;
        }

        if ($compression == 'deep') {
            return trim(str_replace("\n", "", $output));
        } else {
           return "\n".$output."\n";
        }
	}
}