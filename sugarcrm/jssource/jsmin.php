<?php

class JSMin {
    /**
     * Calls the SugarMin minify function.
     *
     * This class is to give backwards compatability to all
     * calls that relied on JSMin
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
    protected $noSpaceChars = array('\\', "$", '_');
    protected $postNewLineSafeChars = array('\\', '$', '_', '{', '[', '(', '+', '-');
    protected $regexOpeners = array('(', '=', '[', ':');
    protected $preNewLineSafeChars = array('\\', '$', '_', '}', ']', ')', '+', '-', '"', "'");
    protected $regexChars = array('(', ',', '=', ':', '[', '!', '&', '|', '?', '{', '}', ';');

    protected $compression;

    // State variables
    protected $inLiteral = false;
    protected $inRegex = false;
    protected $inMlComment = false;
    protected $lastChar = null;
    protected $lastRegexOpener = null;

    private function __construct() {}

    /**
     * Entry point function to minify javascript.
     *
     * @param string $js Javascript source code as a string.
     * @param string $compression Compression option. {light, deep}.
     * @return string $output Output javascript code as a string.
     */
    static public function minify($js, $compression = 'light') {
        try {
            $me = new SugarMin();
            $output = $me->jsParser($js, $compression);
            return $output;
        } catch (Exception $e) {
            // Exception handling is left up to the implementer.
            throw $e;
        }
    }

    /**
     * jsParser will take javascript source code and
     *
     * @param string $js
     * @param string $currentOptions
     * @return void
     */
    protected function jsParser($js, $compression = 'light') {

        // We first perform a few operations to simplify our
        // minification process. We convert all carriage returns
        // into line breaks and delete runs of spacees and line breaks.
        // We also remove multi-line comments.
        $js = str_replace("\r\n", "\n", $js);
        $js = str_replace("\r", "\n", $js);
        $js = preg_replace("/\n+/", "\n", $js);

        // Split our string up into an array and iterate over each line
        // to do processing.
        $input = explode("\n", $js);
        $primedInput = '';

        // In the first pass we will strip out multiline comments and single line comments
        // To allow for easier parsing / processing in the second pass.
        for ($index = 0; $index < count($input); $index++) {
            $line = $input[$index];

            // Remove comments from line.
            $line = $this->removeComments($line);

            $line = trim($line, " \t");

            // If the line is empty, ignore it.
            if (strlen($line) == 0) {
                continue;
            }

            $primedInput[] = $line;
        }

        // Preliminary cleaning up of the code is done, now we move onto
        // advanced parsing / stripping of spaces and literals.

        // Note: With inclusion of some of Brian Nelson's logic,
        // this second pass may no longer be needed. In the event that we
        // make this code public time shoudl be taken to refactor.
        $input = $primedInput;
        $output = '';

        for ($index = 0; $index < count($input); $index++) {
            $line = $input[$index];
            $newLine = '';
            $len = strlen($line);

            $nextLine = ($index < count($input) - 1) ? $input[$index + 1] : '';

            $lastChar = $line[$len - 1];
            $nextChar = ($nextLine) ? $nextLine[0] : null;

            // Iterate through the string one character at a time.
            for ($i = 0; $i < $len; $i++) {
                switch ($line[$i]) {
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
                        if (!((ctype_alnum($line[$i - 1]) || in_array($line[$i - 1], $this->noSpaceChars)) && (in_array($line[$i + 1], $this->noSpaceChars) || ctype_alnum($line[$i + 1])))) {
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
            return "\n" . $output . "\n";
        }
    }

    /**
     * This function removes comments that are embedded
     * within the line. It will check to make sure
     * comment delimiters are not part of any regex literals
     * or string literals.
     *
     * @returns
     */
    protected function removeComments($line) {
        $this->lastChar = null;
        $len = strlen($line);
        $result = '';

        for ($i = 0; $i < $len; $i++) {
            $char = $line[$i];

            // We need to check to see if it's valid to have a regular expression at this point.
            // So we store away the last valid character prior to a regex.
            if (in_array($char, $this->regexOpeners)) {
                $this->lastRegexOpener = $char;
            } else {
                if (!in_array($char, array(' ', "\t", '/'))) {
                    $this->lastRegexOpener = null;
                }
            }

            if ($this->inMlComment) {
                $this->checkMlComment($char);
            } else {
                $regex = $this->checkRegex($line, $i);
                if ($regex !== false) {
                    $result = $result . $regex;
                    $i = $i + strlen($regex) - 1;
                } else {
                    if ($this->checkLiteral($char)) {
                        $result = $result . $char;
                    } else {
                        if ($this->checkComment($char)) {
                            // Return with the result with the / from // removed
                            return substr($result, 0, -1);
                        } else {
                            if ($this->checkMlComment($char)) {
                                // Comment started, strip the opening /
                                $result = substr($result, 0, -1);
                            } else {
                                $result = $result . $char;
                            }
                        }
                    }
                }
            }
            $this->lastChar = $char;
        }
        return $result;
    }

    /**
     * Check to see if we are starting a regular expression literal.
     * @param  $line
     * @param  $i
     * @return bool|string
     */
    protected function checkRegex($line, $i) {
        if ($this->lastRegexOpener !== null) {
            $char = $line[$i];

            if ($char === '/') {
                $len = strlen($line);
                if ($len > $i + 1) {
                    $char2 = $line[$i + 1];
                    if ($char2 !== '*' && $char2 !== '/') {
                        $ret = "/";
                        $escape = 0;
                        for ($j = $i + 1; $j < $len; $j++) {
                            $char = $line[$j];
                            $ret = $ret . $char;
                            switch ($char) {
                                case '/':
                                    if ($escape % 2 == 0) {
                                        return $ret;
                                    }
                                    break;
                                case '\\':
                                    $escape++;
                                    break;
                                default:
                                    $escape = 0;
                                    break;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * Is our character denoting the beginning of a multi-line comment?
     *
     * @param  $char Character
     * @return boolean
     */
    protected function checkMlComment($char) {
        if ($this->inMlComment && (($this->lastChar === '*') && ($char === '/'))) {
            $this->inMlComment = false;
        }
        elseif (($this->lastChar === '/') && ($char === '*'))
        {
            $this->inMlComment = true;
        }
        return $this->inMlComment;
    }

    /**
     * Is our character denoting the beginning of a single line comment?
     * 
     * @param  $char Character to check if it is a single line comment delimiter.
     * @return boolean
     */
    protected function checkComment($char) {
        if (($char === '/') && ($this->lastChar === '/')) {
            // Reset the last char as we should be escaping out of checking now.
            return true;
        }

        return false;
    }

    /**
     * Check to see if the character at a certain position is inside a string literal.
     * Returns true if the character at $position is inside a string literal and false
     * if it isn't.
     * @int position
     * @return boolean
     */
    protected function checkLiteral($char) {
        static $escapes = 0;
        static $literal = '';

        if ($this->inLiteral) {
            if ($char == "\\") {
                $escapes++;
            } else if (($char == $literal) && ($escapes % 2 == 0)) {
                // Flip the literal bit
                $this->inLiteral = (!$this->inLiteral);
            } else {
                $escapes = 0;
            }
        } elseif ($char == '"' || $char == "'") {
            $this->inLiteral = true;
            $literal = $char;
            $escapes = 0;
        }

        return $this->inLiteral;
    }
}