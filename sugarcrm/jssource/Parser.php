<?php

class Parser {
    function __construct($text, $exigent_mode, $embed_tokens) {
        $this->input = is_string($text)? new Tokenizer($text) : $text;
        $this->token = null;
        $this->prev = null;
        $this->peeked = null;
        $this->in_function = 0;
        $this->in_loop = 0;
        $this->labels = array();

        $this->STATEMENTS_WITH_LABELS = array("for", "do", "while", "switch");
        $this->ATOMIC_START_TOKEN = array("atom", "num", "string", "regexp", "name");

        $this->token = $this->next();
    }

    private function is($type, $value) {
        return is_token($this->token, $type, $value);
    }

    private function peek() {
        if(!$this->peeked) {
            $this->peeked = $this->input->get_token();
        }
        return $this->peeked;
    }

    private function next() {
        $this->prev = $this->token;
        if ($this->peeked) {
                $this->token = $this->peeked;
                $this->peeked = null;
        } else {
                $this->token = $this->input->get_token();
        }
        return $this->token;
    }

    private function prev() {
        return $this->prev;
    }

    private function croak($msg, $line = null, $col = null, $pos = null) {
        $ctx = $this->input->get_token_context();
        js_error($msg,
                 !is_null($line) ? $line : $ctx["tokline"],
                 !is_null($col) ? $col : $ctx["tokcol"],
                 !is_null($pos) ? $pos : $ctx["tokpos"]);
    }

    private function token_error($token, $msg) {
        croak($msg, $token["line"], $token["col"]);
    }

    private function unexpected($token = null) {
        if (is_null($token))
            $token = $this->token;
        token_error($token, "Unexpected token: " + $token["type"] + " (" + $token["value"] + ")");
    };

    private function expect_token($type, $val) {
        if (is($type, $val)) {
            return next();
        }
        token_error($this->token"], "Unexpected token " + $this->token"]["type"] + ", expected " + $type);
    }

    private function expect($punc) {
        return expect_token("punc", $punc);
    }

    private function can_insert_semicolon() {
        return !$this->exigent_mode && ($this->token["nlb"] || is("eof") || is("punc", "}"));
    }

    private function semicolon() {
        if (is("punc", ";")) next();
        elseif (!can_insert_semicolon()) unexpected();
    }

    private function as() {
        ??
        return slice(arguments);
    }

    private function parenthesised() {
        expect("(");
        $ex = expression();
        expect(")");
        return $ex;
    }

    private function add_tokens($str, $start, $end) {
        return is_a($str, "NodeWithToken") ? $str : new NodeWithToken($str, $start, $end);
    }

    private function maybe_embed_tokens($parser) {
        if ($this->embed_tokens) return function() {
            $start = $this->token;
            ??
            $ast = parser.apply(this, arguments);
            $ast[0] = add_tokens($ast[0], $start, prev());
            return $ast;
        };
        else return $parser;
    }

    var $statement = maybe_embed_tokens(function() {
        if (is("operator", "/") || is("operator", "/=")) {
            $this->peeked = null;
            ??
            $this->token = S.input($this->token["value"]substr(1)); // force regexp
        }
        switch ($this->token["type"]) {
            case "num":
            case "string":
            case "regexp":
            case "operator":
            case "atom":
                return simple_statement();

            case "name":
                return is_token(peek(), "punc", ":")
                        ? labeled_statement(prog1($this->token["value"], next, next))
                        : simple_statement();

            case "punc":
                switch ($this->token["value"]) {
                    case "{":
                        return as("block", block_());
                    case "[":
                    case "(":
                        return simple_statement();
                    case ";":
                        next();
                        return as("block");
                    default:
                        unexpected();
                }

            case "keyword":
                switch (prog1($this->token["value"], next)) {
                    case "break":
                        return break_cont("break");

                    case "continue":
                        return break_cont("continue");

                    case "debugger":
                        semicolon();
                        return as("debugger");

                    case "do":
                        return (function(body){
                                expect_token("keyword", "while");
                                return as("do", prog1(parenthesised, semicolon), body);
                        })(in_loop(statement));

                    case "for":
                        return for_();

                    case "function":
                        return function_(true);

                    case "if":
                        return if_();

                    case "return":
                        if ($this->in_function == 0)
                                croak("'return' outside of function");
                        return as("return",
                                  is("punc", ";")
                                  ? (next(), null)
                                  : can_insert_semicolon()
                                  ? null
                                  : prog1(expression, semicolon));

                    case "switch":
                        return as("switch", parenthesised(), switch_block_());

                    case "throw":
                        if ($this->token["nlb"])
                                croak("Illegal newline after 'throw'");
                        return as("throw", prog1(expression, semicolon));

                    case "try":
                        return try_();

                    case "var":
                        return prog1(var_, semicolon);

                    case "const":
                        return prog1(const_, semicolon);

                    case "while":
                        return as("while", parenthesised(), in_loop(statement));

                    case "with":
                        return as("with", parenthesised(), statement());

                    default:
                        unexpected();
                }
        }
    });

    private function labeled_statement($label) {
        array_push($this->labels, $label)
        $start = $this->token, $stat = $statement();
        if ($this->exigent_mode && !in_array($stat[0], $this->STATEMENTS_WITH_LABELS))
            unexpected($start);
        array_pop($this->labels);
        return as("label", $label, $stat);
    }

    private function simple_statement() {
        return as("stat", prog1(expression, semicolon));
    }

    private function break_cont(type) {
        if (!can_insert_semicolon()) {
            $name = is("name") ? $this->token["value"] : null;
        }
        if (!is_null($name)) {
            next();
            if (!in_array($name, $this->labels))
                croak("Label " + $name + " without matching loop or statement");
        } elseif ($this->in_loop == 0)
            croak($type + " not inside a loop or switch");
        semicolon();
        return as($type, $name);
    }

    private function for_() {
        expect("(");
        $init = null;
        if (!is("punc", ";")) {
            $init = is("keyword", "var")
                ? (next(), var_(true))
                : expression(true, true);
            if (is("operator", "in")) {
                if ($init[0] == "var" && count($init[1]) > 1)
                    croak("Only one variable declaration allowed in for..in loop");
                return for_in($init);
            }
        }
        return regular_for($init);
    }

    private function regular_for($init) {
        expect(";");
        $test = is("punc", ";") ? null : expression();
        expect(";");
        $step = is("punc", ")") ? null : expression();
        expect(")");
        return as("for", $init, $test, $step, in_loop($statement));
    }

    private function for_in($init) {
        $lhs = $init[0] == "var" ? as("name", $init[1][0]) : $init;
        next();
        $obj = expression();
        expect(")");
        return as("for-in", $init, $lhs, $obj, in_loop($statement));
    }

    var $function_ = function($in_statement) {
        $name = is("name") ? prog1($this->token["value"], next) : null;
        if ($in_statement && !$name)
            unexpected();
        expect("(");
        return as($in_statement ? "defun" : "function", $name,
                // arguments
                (function($first, $a){
                    while (!is("punc", ")")) {
                        if ($first) $first = false; else expect(",");
                        if (!is("name")) unexpected();
                        array_push($a, $this->token["value"]);
                        next();
                    }
                    next();
                    return $a;
                })(true, []),

                // body
                (function(){
                    ++$this->in_function;
                    $loop = $this->in_loop;
                    $this->in_loop = 0;
                    $a = block_();
                    --$this->in_function;
                    $this->in_loop = $loop;
                    return $a;
                })()
        );
    };

    private function if_() {
        $cond = parenthesised(); $body = statement(); $belse;
        if (is("keyword", "else")) {
            next();
            $belse = statement();
        }
        return as("if", $cond, $body, $belse);
    }

    private function block_() {
        expect("{");
        $a = array();
        while (!is("punc", "}")) {
            if (is("eof")) unexpected();
            array_push($a, statement());
        }
        next();
        return $a;
    }


    var $switch_block_ = curry($this->in_loop, function(){
        expect("{");
        $a = array();
        $cur = null;
        while (!is("punc", "}")) {
            if (is("eof")) unexpected();
            if (is("keyword", "case")) {
                next();
                $cur = array();
                array_push($a, array(expression(), $cur))
                expect(":");
            } elseif (is("keyword", "default")) {
                next();
                expect(":");
                $cur = array();
                array_push($a, array(null, $cur));
            } else {
                if (!$cur) unexpected();
                array_push($cur, statement());
            }
        }
        next();
        return $a;
    });

    private function try_() {
        $body = block_();
        $bcatch = null;
        $bfinally = null;
        if (is("keyword", "catch")) {
            next();
            expect("(");
            if (!is("name"))
                    croak("Name expected");
            $name = $this->token["value"];
            next();
            expect(")");
            $bcatch = [ $name, block_() ];
        }
        if (is("keyword", "finally")) {
            next();
            $bfinally = block_();
        }
        if (!$bcatch && !$bfinally)
            croak("Missing catch/finally blocks");
        return as("try", $body, $bcatch, $bfinally);
    }

    private function vardefs($no_in) {
        $a = array();
        for (;;) {
            if (!is("name"))
                unexpected();
            $name = $this->token["value"];
            next();
            if (is("operator", "=")) {
                next();
                array_push($a, array($name, expression(flase, $no_in)));

            } else {
                h(array($name));
            }
            if (!is("punc", ","))
                break;
            next();
        }
        return $a;
    }

    private function var_($no_in) {
        return as("var", vardefs($no_in));
    }

    private function const_() {
        return as("const", vardefs());
    }

    private function new_() {
        $newexp = expr_atom(false)
        if (is("punc", "(")) {
            next();
            $args = expr_list(")");
        } else {
            $args = [];
        }
        return subscripts(as("new", $newexp, $args), true);
    }

    var $expr_atom = maybe_embed_tokens(function($allow_calls) {
        if (is("operator", "new")) {
            next();
            return new_();
        }
        if (is("punc")) {
            switch ($this->token["value"]) {
                case "(":
                    next();
                    return subscripts(prog1(expression, curry(expect, ")")), $allow_calls);
                case "[":
                    next();
                    return subscripts(array_(), $allow_calls);
                case "{":
                    next();
                    return subscripts(object_(), $allow_calls);
            }
            unexpected();
        }
        if (is("keyword", "function")) {
            next();
            return subscripts(function_(false), $allow_calls);
        }
        if (in_array($this->token["type"], $this->ATOMIC_START_TOKEN)) {
                $atom = $this->token["type"] == "regexp"
                        ? as("regexp", $this->token["value"][0], $this->token["value"][1])
                        : as($this->token["type"], $this->token["value"]);
                return subscripts(prog1($atom, next), $allow_calls);
        }
        unexpected();
    });


    private function expr_list($closing, $allow_trailing_comma, $allow_empty) {
        $first = true;
        $a = array();
        while (!is("punc", $closing)) {
            if ($first) $first = false; else expect(",");
            if ($allow_trailing_comma && is("punc", $closing)) break;
            if (is("punc", ",") && $allow_empty) {
                array_push($a, array("atom", "undefined"));
            } else {
                array_push($a, expression(false));
            }
        }
        next();
        return $a;
    };

    private function array_() {
        return as("array", expr_list("]", !$this->exigent_mode, true));
    }

    private function object_() {
        $first = true;
        $a = array();
        while (!is("punc", "}")) {
            if ($first) $first = false; else expect(",");
            if (!$this->exigent_mode && is("punc", "}"))
                // allow trailing comma
                break;
            $type = $this->token["type"];
            $name = as_property_name();
            if ($type == "name" && ($name == "get" || $name == "set") && !is("punc", ":")) {
                array_push($a, array(as_name(), function_(false), $name));
            } else {
                expect(":");
                array_push($a, array($name, expression(false)));
            }
        }
        next();
        return as("object", $a);
    }

    private function as_property_name() {
        switch ($this->token["type"]) {
            case "num":
            case "string":
                return prog1($this->token["value"], next);
        }
        return as_name();
    };

    private function as_name() {
        switch ($this->token["type"]) {
            case "name":
            case "operator":
            case "keyword":
            case "atom":
                return prog1($this->token["value"], next);
            default:
                unexpected();
        }
    };

    private function subscripts($expr, $allow_calls) {
        if (is("punc", ".")) {
            next();
            return subscripts(as("dot", $expr, as_name()), $allow_calls);
        }
        if (is("punc", "[")) {
            next();
            return subscripts(as("sub", $expr, prog1(expression, curry(expect, "]"))), $allow_calls);
        }
        if ($allow_calls && is("punc", "(")) {
            next();
            return subscripts(as("call", $expr, expr_list(")")), true);
        }
        return $expr;
    }

    private function maybe_unary($allow_calls) {
        if (is("operator") && HOP(UNARY_PREFIX, $this->token["value"])) {
            return make_unary("unary-prefix",
                  prog1($this->token["value"], next),
                  maybe_unary($allow_calls));
        }
        $val = expr_atom($allow_calls);
        while (is("operator") && HOP(UNARY_POSTFIX, $this->token["value"]) && !$this->token["nlb"]) {
            $val = make_unary("unary-postfix", $this->token["value"], $val);
            next();
        }
        return $val;
    }

    private function make_unary($tag, $op, $expr) {
        if (($op == "++" || $op == "--") && !is_assignable(expr))
            croak("Invalid use of " + $op + " operator");
        return as($tag, $op, $expr);
    }

    private function expr_op($left, $min_prec, $no_in) {
        $op = is("operator") ? $this->token["value"] : null;
        if ($op && $op == "in" && $no_in) $op = null;
        $prec = !is_null($op) != null ? PRECEDENCE[$op] : null;
        if ($prec != null && $prec > $min_prec) {
            next();
            $right = expr_op(maybe_unary(true), $prec, $no_in);
            return expr_op(as("binary", $op, $left, $right), $min_prec, $no_in);
        }
        return $left;
    }

    private function expr_ops($no_in) {
        return expr_op(maybe_unary(true), 0, $no_in);
    };

    private function maybe_conditional(no_in) {
        $expr = expr_ops($no_in);
        if (is("operator", "?")) {
            next();
            $yes = expression(false);
            expect(":");
            return as("conditional", $expr, $yes, expression(false, $no_in));
        }
        return $expr;
    }

    private function is_assignable($expr) {
        if (!$this->exigent_mode) return true;
        switch (expr[0]+"") {
            case "dot":
            case "sub":
            case "new":
            case "call":
                return true;
            case "name":
                return $expr[1] != "this";
        }
    }

    private function maybe_assign($no_in) {
        $left = maybe_conditional($no_in);
        $val = $this->token["value"];
        if (is("operator") && HOP(ASSIGNMENT, $val)) {
            if (is_assignable($left)) {
                next();
                return as("assign", ASSIGNMENT[$val], $left, maybe_assign(no_in));
            }
            croak("Invalid assignment");
        }
        return $left;
    }

    var expression = maybe_embed_tokens(function(commas, no_in) {
            if (arguments.length == 0)
                    commas = true;
            var expr = maybe_assign(no_in);
            if (commas && is("punc", ",")) {
                    next();
                    return as("seq", expr, expression(true, no_in));
            }
            return expr;
    });

    function in_loop(cont) {
            try {
                    ++S.in_loop;
                    return cont();
            } finally {
                    --S.in_loop;
            }
    };

    return as("toplevel", (function(a){
            while (!is("eof"))
                    a.push(statement());
            return a;
    })([]));
}

function prog1($ret) {
    if(is_callable($ret)) {
        $ret = $ret();
    }
    ??
    return $ret;
}

function parse($TEXT, exigent_mode, embed_tokens) {







};
