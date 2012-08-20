<?php
/**
 * JavaScript Tokenizer
 */
class JSTokenizer {
    
    private $line;
    private $col;
    private $divmode;
    private $src;
    private $whitespace;
    private $unicode;

    private $regRegex;
    private $regDQuote;
    private $regSQuote;
    private $regWord;
    private $regWhite;
    private $regBreak;
    private $regJunk;
    private $regLines;
    private $regNumber;
    private $regComment;
    private $regCommentMulti;
    
    private $regPunc = '/(?:\>\>\>\=|\>\>\>|\<\<\=|\>\>\=|\!\=\=|\=\=\=|&&|\<\<|\>\>|\|\||\*\=|\|\=|\^\=|&\=|%\=|-\=|\+\+|\+\=|--|\=\=|\>\=|\!\=|\<\=|;|,|\<|\>|\.|\]|\}|\(|\)|\[|\=|\:|\||&|-|\{|\^|\!|\?|\*|%|~|\+)/';

    /**
     * @var JSLex
     */
    private $Lex;
    
    
    /**
     * Constructor
     */ 
    function __construct( $whitespace = true, $unicode = true ){
        
        $this->Lex = new JSLex;
        
        $this->whitespace = $whitespace;
        $this->unicode = $unicode;
        
        // Prepare regular Expressions that differ according to whether Unicode support is on
        if( $this->unicode ){
            $this->regRegex = '!^/(?:\\\\.|[^\r\n\p{Zl}\p{Zp}/\\\\])+/[gi]*!u';
            $this->regDQuote = '/^"(?:\\\\.|[^\r\n\p{Zl}\p{Zp}"\\\\])*"/u';
            $this->regSQuote = "/^'(?:\\\\.|[^\r\n\p{Zl}\p{Zp}'\\\\])*'/u";
            $this->regWord  = '/^(?:\\\\u[0-9A-F]{4,4}|[\$_\pL\p{Nl}])(?:\\\\u[0-9A-F]{4,4}|[\$_\pL\pN\p{Mn}\p{Mc}\p{Pc}])*/ui';
            $this->regWhite  = '/^[\x20\x09\x0B\x0C\xA0\p{Zs}]+/u';
            $this->regBreak  = '/^[\r\n\p{Zl}\p{Zp}]+/u';
            $this->regJunk   = '/^./u';
            $this->regLines  = '/(\r\n|[\r\n\p{Zl}\p{Zp}])/u';
        }
        else {
            $this->regRegex = '!^/(?:\\\\.|[^\r\n/\\\\])+/[gi]*!';
            $this->regDQuote = '/^"(?:\\\\.|[^\r\n"\\\\])*"/';
            $this->regSQuote = "/^'(?:\\\\.|[^\r\n'\\\\])*'/";
            $this->regWord  = '/^[\$_A-Z][\$_A-Z0-9]*/i';
            $this->regWhite  = '/^[\x20\x09\x0B\x0C\xA0]+/';
            $this->regBreak  = '/^[\r\n]+/';
            $this->regJunk   = '/^./';
            $this->regLines  = '/(\r\n|\r|\n)/';
        }

        // Other patterns are constant
        $this->regNumber = '/^(?:0x[A-F0-9]+|\d*\.\d+(?:E(?:\+|\-)?\d+)?|\d+)/i';
        $this->regComment = '/^\/\/.*/';
        $this->regCommentMulti = '/^\/\*.*\*\//Us';
    }
    
    
    /**
     * @param src
     * @return void
     */
    function init( $src ){

        $this->src = $src;
        
        // remember [line,character] offset
        $this->line = 1;
        $this->col = 1;
        
        // We need to switch between RegExp and Div modes depending on context
        // 1. InputElementDiv, where a divide symbol is permitted so `/' will match `/' or `/='
        // 2. InputElementRegExp, where a regular expression literal is permitted
        $this->divmode = false;
        
    }
    
    
    

    /**
     * @var string
     * @return array
     */  
    function get_all_tokens( $src ){
        $this->init( $src );
        // iterate until source exhausted
        $tokens = array();
        while( $token = $this->get_next_token() ){
            $tokens[] = $token;
        }
        return $tokens;
    }
    

    
    
    
    /**
     * @return array
     */
    function get_next_token(){
                
        // Sniff current leading character to save unecessary regular expression failures
        $c = $this->src{0};
        if( $c == null ){
            return null;  
        }
        
        // Encapsed string
        // ( double quotes )
        if( $c === '"' ){
            if( ! preg_match( $this->regDQuote, $this->src, $r ) ){
                throw new Exception( "Unterminated string constant on line $this->line" );
                $s = $t = '"';
            }
            else {
                $s = $r[0];
                $t = J_STRING_LITERAL;
            }
            $this->divmode = true;
        }
        // ( single quotes )
        else if( $c === "'" ){
            if( ! preg_match( $this->regSQuote, $this->src, $r ) ){
                throw new Exception( "Unterminated string constant on line $this->line" );
                $s = $t = "'";
            }
            else {
                $s = $r[0];
                $t = J_STRING_LITERAL;
            }
            $this->divmode = true;
        }

        else if( $c === '/' ){
            //  Single line comments 
            if( $this->src{1} === '/' && preg_match( $this->regComment, $this->src, $r ) ){
                $t = $this->whitespace ? J_COMMENT : false;
                $s = $r[0];
            }
            
            // Multi line comments 
            else if( $this->src{1} === '*' && preg_match( $this->regCommentMulti, $this->src, $r ) ){
                $s = $r[0];
                if( $this->whitespace ){
                    $t = J_COMMENT;
                }
                // multline comment must be replaced with line terminator for the purpose of automatic semicolon insertion
                else {
                    $breaks = preg_match( $this->regLines, $s, $r );
                    $t = $breaks ? J_LINE_TERMINATOR : false;
                }
            }           
                    
            // Regular Expression literals
            else if( ! $this->divmode ){
                if( ! preg_match( $this->regRegex, $this->src, $r ) ){
                    throw new Exception( "Bad regular expression literal on line $this->line");   
                    $s = $t = '/';
                    $this->divmode = false;
                }
                else {
                    $s = $r[0];
                    $t = J_REGEX;
                    $this->divmode = true;
                }
            }

            // Else one of the two dividing operators
            else if ( $this->src{1} === '=' ) {
                $s = $t = '/=';
                $this->divmode = false;
            }
            else {
                $s = $t = '/';
                $this->divmode = false;
            }
        }
        
        // all other patterns will use full regular expressions
        // but ensure to use least expensive patterns first
        
        // Line terminator
        else if( preg_match( $this->regBreak, $this->src, $r ) ){
            $t = J_LINE_TERMINATOR;
            $s = $r[0];
            $this->divmode = false;
        }
        
        // Any other (non breaking) whitespace
        else if( preg_match( $this->regWhite, $this->src, $r ) ){
            $t = $this->whitespace ? J_WHITESPACE : false;
            $s = $r[0];
        }

        // Numbers
        // May not start with +/-, this is handled as a unary operator in the syntactical grammar
        else if( preg_match( $this->regNumber, $this->src, $r ) ){
            $t = J_NUMERIC_LITERAL;
            $s = $r[0];
            // regexp not permitted after numeric literal, but division is
            $this->divmode = true;
        }
                
        // Identifiers
        // 
        else if( preg_match( $this->regWord, $this->src, $r ) ){
            $s = $r[0];
            $t = $this->Lex->is_word($s) or $t = J_IDENTIFIER;
                
            switch( $t ){
            // divide symbol may follow identifiers
            case J_IDENTIFIER;
                $this->divmode = true;
                break;
            // neither div or regexp are likely to follow a keyword
            default:
                $this->divmode = null;
            }
        }       
        
        // Punctuators
        //
        else if ( preg_match( $this->regPunc, $this->src, $r ) ){
            $s = $t = $r[0];
            // switch divmode
            switch( $t ){
            // divide allowed after bracketed expressions, otherwise assume punctuator, or operator.
            // *WARNING* context engine not strong enough here, errors could occur as follows "{a:'apple'} / x"
            case ']':
            case ')':
                $this->divmode = true;
                break;
            default:
                $this->divmode = false;
            }
        }           
        
        // else token is junk
        else {
            preg_match( $this->regJunk, $this->src, $r );
            $s = $t = isset($r[0]) ? $r[0] : $c;
            throw new Exception("Junk on line $this->line, ".var_export($s,1) );
        }
        
        // sanity check avoids infinite loops
        $len = strlen( $s );
        if( $len === 0 ){
            throw new Exception('Failed to extract anything');
        }
        
        // push complete token unless deliberately ignoring
        if( $t !== false ){
            $token = array ( 
                $t,           // <- [0] scalar terminal symbol ( J_* )
                $s,           // <- [1] string value of token in source code
                $this->line,  // <- [2] starting line number of token
                $this->col    // <- [3] starting column number
            );
        }
        
        // truncate source for next pass
        $this->src = substr( $this->src, $len );

        // calculate line and column number for next pass
        if( $t === J_LINE_TERMINATOR || $t === J_COMMENT ){
            $this->line += preg_match_all( $this->regLines, $s, $r );
            // find relative character position to ascertain column number
            $cbreak = end( $r[0] );
            $this->col = $len - strrpos( $s, $cbreak );
        }
        else {
            $this->col += $len;
        }
        
        // return token if set
        return isset($token) ? $token : null;
    }

    
}







