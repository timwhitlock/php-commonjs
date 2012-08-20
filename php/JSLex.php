<?php
/**
 * 
 */
 
define( 'J_FUNCTION', 1 );
define( 'J_IDENTIFIER', 2 );
define( 'J_VAR', 3 );
define( 'J_IF', 4 );
define( 'J_ELSE', 5 );
define( 'J_DO', 6 );
define( 'J_WHILE', 7 );
define( 'J_FOR', 8 );
define( 'J_IN', 9 );
define( 'J_CONTINUE', 10 );
define( 'J_BREAK', 11 );
define( 'J_RETURN', 12 );
define( 'J_WITH', 13 );
define( 'J_SWITCH', 14 );
define( 'J_CASE', 15 );
define( 'J_DEFAULT', 16 );
define( 'J_THROW', 17 );
define( 'J_TRY', 18 );
define( 'J_CATCH', 19 );
define( 'J_FINALLY', 20 );
define( 'J_THIS', 21 );
define( 'J_STRING_LITERAL', 22 );
define( 'J_NUMERIC_LITERAL', 23 );
define( 'J_TRUE', 24 );
define( 'J_FALSE', 25 );
define( 'J_NULL', 26 );
define( 'J_REGEX', 27 );
define( 'J_NEW', 28 );
define( 'J_DELETE', 29 );
define( 'J_VOID', 30 );
define( 'J_TYPEOF', 31 );
define( 'J_INSTANCEOF', 32 );
define( 'J_COMMENT', 33 );
define( 'J_WHITESPACE', 34 );
define( 'J_LINE_TERMINATOR', 35 );
define( 'J_ABSTRACT', 36 );
define( 'J_ENUM', 37 );
define( 'J_INT', 38 );
define( 'J_SHORT', 39 );
define( 'J_BOOLEAN', 40 );
define( 'J_EXPORT', 41 );
define( 'J_INTERFACE', 42 );
define( 'J_STATIC', 43 );
define( 'J_BYTE', 44 );
define( 'J_EXTENDS', 45 );
define( 'J_LONG', 46 );
define( 'J_SUPER', 47 );
define( 'J_CHAR', 48 );
define( 'J_FINAL', 49 );
define( 'J_NATIVE', 50 );
define( 'J_SYNCHRONIZED', 51 );
define( 'J_CLASS', 52 );
define( 'J_FLOAT', 53 );
define( 'J_PACKAGE', 54 );
define( 'J_THROWS', 55 );
define( 'J_CONST', 56 );
define( 'J_GOTO', 57 );
define( 'J_PRIVATE', 58 );
define( 'J_TRANSIENT', 59 );
define( 'J_DEBUGGER', 60 );
define( 'J_IMPLEMENTS', 61 );
define( 'J_PROTECTED', 62 );
define( 'J_VOLATILE', 63 );
define( 'J_DOUBLE', 64 );
define( 'J_IMPORT', 65 );
define( 'J_PUBLIC', 66 );
define( 'J_PROGRAM', 67 );
define( 'J_ELEMENTS', 68 );
define( 'J_ELEMENT', 69 );
define( 'J_STATEMENT', 70 );
define( 'J_FUNC_DECL', 71 );
define( 'J_PARAM_LIST', 72 );
define( 'J_FUNC_BODY', 73 );
define( 'J_FUNC_EXPR', 74 );
define( 'J_BLOCK', 75 );
define( 'J_VAR_STATEMENT', 76 );
define( 'J_EMPTY_STATEMENT', 77 );
define( 'J_EXPR_STATEMENT', 78 );
define( 'J_IF_STATEMENT', 79 );
define( 'J_ITER_STATEMENT', 80 );
define( 'J_CONT_STATEMENT', 81 );
define( 'J_BREAK_STATEMENT', 82 );
define( 'J_RETURN_STATEMENT', 83 );
define( 'J_WITH_STATEMENT', 84 );
define( 'J_LABELLED_STATEMENT', 85 );
define( 'J_SWITCH_STATEMENT', 86 );
define( 'J_THROW_STATEMENT', 87 );
define( 'J_TRY_STATEMENT', 88 );
define( 'J_STATEMENT_LIST', 89 );
define( 'J_VAR_DECL_LIST', 90 );
define( 'J_VAR_DECL', 91 );
define( 'J_VAR_DECL_LIST_NO_IN', 92 );
define( 'J_VAR_DECL_NO_IN', 93 );
define( 'J_INITIALIZER', 94 );
define( 'J_INITIALIZER_NO_IN', 95 );
define( 'J_ASSIGN_EXPR', 96 );
define( 'J_ASSIGN_EXPR_NO_IN', 97 );
define( 'J_EXPR', 98 );
define( 'J_EXPR_NO_IN', 99 );
define( 'J_LHS_EXPR', 100 );
define( 'J_CASE_BLOCK', 101 );
define( 'J_CASE_CLAUSES', 102 );
define( 'J_CASE_DEFAULT', 103 );
define( 'J_CASE_CLAUSE', 104 );
define( 'J_CATCH_CLAUSE', 105 );
define( 'J_FINALLY_CLAUSE', 106 );
define( 'J_PRIMARY_EXPR', 107 );
define( 'J_ARRAY_LITERAL', 108 );
define( 'J_OBJECT_LITERAL', 109 );
define( 'J_ELISION', 110 );
define( 'J_ELEMENT_LIST', 111 );
define( 'J_PROP_LIST', 112 );
define( 'J_PROP_NAME', 113 );
define( 'J_MEMBER_EXPR', 114 );
define( 'J_ARGS', 115 );
define( 'J_NEW_EXPR', 116 );
define( 'J_CALL_EXPR', 117 );
define( 'J_ARG_LIST', 118 );
define( 'J_POSTFIX_EXPR', 119 );
define( 'J_UNARY_EXPR', 120 );
define( 'J_MULT_EXPR', 121 );
define( 'J_ADD_EXPR', 122 );
define( 'J_SHIFT_EXPR', 123 );
define( 'J_REL_EXPR', 124 );
define( 'J_REL_EXPR_NO_IN', 125 );
define( 'J_EQ_EXPR', 126 );
define( 'J_EQ_EXPR_NO_IN', 127 );
define( 'J_BIT_AND_EXPR', 128 );
define( 'J_BIT_AND_EXPR_NO_IN', 129 );
define( 'J_BIT_XOR_EXPR', 130 );
define( 'J_BIT_XOR_EXPR_NO_IN', 131 );
define( 'J_BIT_OR_EXPR', 132 );
define( 'J_BIT_OR_EXPR_NO_IN', 133 );
define( 'J_LOG_AND_EXPR', 134 );
define( 'J_LOG_AND_EXPR_NO_IN', 135 );
define( 'J_LOG_OR_EXPR', 136 );
define( 'J_LOG_OR_EXPR_NO_IN', 137 );
define( 'J_COND_EXPR', 138 );
define( 'J_COND_EXPR_NO_IN', 139 );
define( 'J_ASSIGN_OP', 140 );
define( 'J_IGNORE', 141 );
define( 'J_RESERVED', 142 ); 
 
 
/**
 * Javascript Lex object.
 */
class JSLex {

    /**
     * @var array
     */
    private $words = array (
        // Literals
        'true'   => J_TRUE,
        'false'  => J_FALSE,
        'null'   => J_NULL,
        // Keyword symbols
        'break'    => J_BREAK,     'else'       => J_ELSE,        'new'    => J_NEW,     'var'   => J_VAR,  
        'case'     => J_CASE,      'finally'    => J_FINALLY,     'return' => J_RETURN,  'void'  => J_VOID,  
        'catch'    => J_CATCH,     'for'        => J_FOR,         'switch' => J_SWITCH,  'while' => J_WHILE,  
        'continue' => J_CONTINUE,  'function'   => J_FUNCTION,    'this'   => J_THIS,    'with'  => J_WITH,  
        'default'  => J_DEFAULT,   'if'         => J_IF,          'throw'  => J_THROW,  
        'delete'   => J_DELETE,    'in'         => J_IN,          'try'    => J_TRY,  
        'do'       => J_DO,        'instanceof' => J_INSTANCEOF,  'typeof' => J_TYPEOF,
        // Reserved symbols
        'abstract' => J_ABSTRACT,  'enum'       => J_ENUM,       'int'       => J_INT,        'short'        => J_SHORT,  
        'boolean'  => J_BOOLEAN,   'export'     => J_EXPORT,     'interface' => J_INTERFACE,  'static'       => J_STATIC,  
        'byte'     => J_BYTE,      'extends'    => J_EXTENDS,    'long'      => J_LONG,       'super'        => J_SUPER,  
        'char'     => J_CHAR,      'final'      => J_FINAL,      'native'    => J_NATIVE,     'synchronized' => J_SYNCHRONIZED,  
        'class'    => J_CLASS,     'float'      => J_FLOAT,      'package'   => J_PACKAGE,    'throws'       => J_THROWS,  
        'const'    => J_CONST,     'goto'       => J_GOTO,       'private'   => J_PRIVATE,    'transient'    => J_TRANSIENT,  
        'debugger' => J_DEBUGGER,  'implements' => J_IMPLEMENTS, 'protected' => J_PROTECTED,  'volatile'     => J_VOLATILE,  
        'double'   => J_DOUBLE,    'import'     => J_IMPORT,     'public'    => J_PUBLIC, 
    );


    /**
     * @param string
     * @return bool
     */
    function is_word( $s ){
        return isset($this->words[$s]) ? $this->words[$s] : false;
    }
        
    

}
