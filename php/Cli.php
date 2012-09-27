<?php
/**
 * Command Line Interface tools
 */
abstract class Cli {

    /**
     * @var array
     */ 
    private static $args = array();
    
    /**
     * Registered args
     * @var array
     */
    private static $_args = array();
    
    /**
     * Registered flags
     * @var array
     */
    private static $_args_idx = array();
    


    /**
     * Initialize environment
     * @todo research CGI environment, differences to cli
     * @return Void
     */
    static function init() {

        switch( PHP_SAPI ) {
        // Ideally we want to be runnning as CLI
        case 'cli':
            break;
        // Special conditions to ensure CGI runs as CLI
        case 'cgi':
            // Ensure resource constants are defined
            if( ! defined('STDERR') ){
                define( 'STDERR', fopen('php://stderr', 'w') );
            }
            if( ! defined('STDOUT') ){
                define( 'STDOUT', fopen('php://stdout', 'w') );
            }
            break;
        default:
            echo "Command line only\n";
            exit(1);
        }
        
        // parse command line arguments from argv global
        global $argv, $argc;
        // first cli arg is always current script. second arg will be script arg passed to shell wrapper
        for( $i = 1; $i < $argc; $i++ ){
            $arg = $argv[ $i ];
            
            // Each command line argument may take following forms:
            //  1. "Any single argument", no point parsing this unless it follows #2 below
            //  2. "-aBCD", one or more switches, parse into 'a'=>true, 'B'=>true, and so on
            //  3. "-a value", flag used with following value, parsed to 'a'=>'value'
            //  4. "--longoption", GNU style long option, parse into 'longoption'=>true
            //  5. "--longoption=value", as above, but parse into 'longoption'=>'value'
            //  6."any variable name = any value" 
            
            $pair = explode( '=', $arg, 2 );
            if( isset($pair[1]) ){
                $name = trim( $pair[0] );
                if( strpos($name,'--') === 0 ){
                    // #5. trimming "--" from option, tough luck if you only used one "-"
                    $name = trim( $name, '-' );
                }
                // else is #6, any pair
                $name and self::$args[$name] = trim( $pair[1] );
            }
            
            else if( strpos($arg,'--') === 0 ){
                // #4. long option, no value
                $name = trim( $arg, "-\n\r\t " );
                $name and self::$args[ $name ] = true;
            }
            
            else if( $arg && $arg{0} === '-' ){
                $flags = preg_split('//', trim($arg,"-\n\r\t "), -1, PREG_SPLIT_NO_EMPTY );
                foreach( $flags as $flag ){
                    self::$args[ $flag ] = true;
                }
                // leave $flag set incase a value follows.
                continue;
            }

            // else is a standard argument. use as value only if it follows a flag, e.g "-a apple"
            else if( isset($flag) ){
                self::$args[ $flag ] = trim( $arg );
            }

            // dispose of last flag
            unset( $flag );
            // next arg 
        }
    }   
    
    
    /**
     * Register a command line argument
     */
    public static function register_arg( $short, $long = '', $desc = '', $mandatory = false ){
        $i = count(self::$_args);
        self::$_args[$i] = func_get_args();
        $short and self::$_args_idx[$short] = $i;
        $long  and self::$_args_idx[$long]  = $i;
    }
    
    
    /**
     * Check arguments registered with register_args()
     */
    public static function validate_args(){
       // exit if -h or --help is non-empty
       if( self::arg('h', self::arg('help') ) ){
           self::exit_help();
       }
       // exit if a mandatory argument not found
       foreach( self::$_args as $r ){
           if( ! $r[3] ){
               continue; // <- not mandatory
           }
           list( $short, $long, $desc ) = $r;
           if( ( $short && is_null(self::arg($short)) ) || ( $long && is_null(self::arg($long)) ) ){
               $name = $long or $name = $short;
               self::stderr("Argument required '%s' (%s)\n", $name, $desc );
               self::exit_help();
           }
       }
       // exit if invalid argument found. This helps avoid mistakes
       foreach( self::$args as $flag => $value ){
           if( ! isset(self::$_args_idx[$flag]) ){
               self::stderr("Unexpected argument '%s' \n", $flag);
               self::exit_help();
           }
       }    
    }
    
    
    /**
     * exit with usage dump
     */
    public static function exit_help(){
       $usage = 'Usage: php -f '.basename(self::arg(0));
       if( self::$_args ){
           $usage .= ' -- <arguments> ';
           $table = array();
           $widths = array( 0, 0, 0 );
           foreach( self::$_args as $r => $row ){
               $short = ($row[3]?'* ':'  ') . ( $row[0] ? ' -'.$row[0] : '' );
               $long  = $row[1] ? '  --'.$row[1] : '';
               $desc  = $row[2] ? '   '.$row[2] : '';
               $widths[0] = max( $widths[0], strlen($short) );
               $widths[1] = max( $widths[1], strlen($long) );
               $widths[2] = max( $widths[2], strlen($desc) );
               $table[] = array( $short, $long, $desc );
           }
           foreach( $table as $row ){
               $usage .= "\n";
               foreach( $row as $i => $val ){
                   $usage .= str_pad($val,$widths[$i]);
               }
           }
       }
       self::stderr( $usage."\n" );
       exit(0);
    }
    
    
    
    /**
     * Get command line argument
     * @param int|string argument name or index
     * @param string optional default argument to return if not present
     * @return string
     */ 
    public static function arg( $a, $default = null ){
        if( is_int($a) ){
            global $argv;
            // note: arg(0) will always be the script path
            return isset($argv[$a]) ? $argv[$a] : $default;
        }
        if( isset(self::$args[$a]) ){
            return self::$args[$a];
        }
        // not found. try aliases
        if( isset(self::$_args_idx[$a]) ) {
            $r = self::$_args[ self::$_args_idx[$a] ];
            // trying short when long not found
            if( $r[0] && $a !== $r[0] && isset(self::$args[$r[0]]) ){
                return self::$args[$r[0]];
            }
            // trying long when short not found
            else if( $r[1] && $a !== $r[1] && isset(self::$args[$r[1]]) ){
                return self::$args[$r[1]];
            }
        }
        // give up
        return $default;
    }
    
    
    
    /**
     * Print to stderr
     * @param string printf style formatter
     * @param ... arguments to printf
     * @return void
     */
    public static function stderr( $s ){
        if( func_num_args() > 1 ){
            $args = func_get_args();
            $s = call_user_func_array( 'sprintf', $args );
        }
        fwrite( STDERR, $s );
    }
    
    
    /**
     * 
     */
    public static function death( $msg = '' ){
       if( $msg ){
           $args = func_get_args();
           $args[0] .= "\n";
           call_user_func_array(array(__CLASS__,'stderr'),$args);
       }
       exit(1);
    }

    
    
    /**
     * Print to stdout
     * @param string printf style formatter
     * @param ... arguments to printf
     * @return void
     */
    public static function stdout( $s ){
        if( func_num_args() > 1 ){
            $args = func_get_args();
            $s = call_user_func_array( 'sprintf', $args );
        }
        echo $s;
    }
    
    
    
    
    /**
     * Execute java -jar file
     * @return string output from java program
     */
    public static function exec_jar( $jar, array $args, $stdin = '' ){

        // ensure java runtime - no point checking, cos it may be outside php open_basedir
        $java = WHICH_JAVA or $path = '/usr/bin/java';
        
        $descriptorspec = array (
            1 => array('pipe', 'w'), // stdout
            2 => STDERR,
        );
        if( $stdin ){
            $descriptorspec[0] = array('pipe', 'r'); // stdin is a pipe that the child will read from
        }
        $cmd = $java.' -jar '.escapeshellarg($jar);
        foreach( $args as $arg ){
            $cmd .= ' '.escapeshellarg($arg);
        }
        $process = proc_open( $cmd, $descriptorspec, $pipes );
        if( ! $process ){
            throw new Exception('Failed to open process to '.$cmd);
        }
        // Pipe in to STDIN
        if( $stdin ){
            fwrite($pipes[0], $stdin);
            fclose($pipes[0]);
        }
        // Get response as it is piped out
        if( isset($pipes[2]) ){
            $stderr = stream_get_contents($pipes[2], 1024 );// and self::stderr($stderr);
            fclose($pipes[2]);
        }
        if( isset($pipes[1]) ){
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
        }
        else {
            $stdout = '';
        }
        // close and return binary captured data
        $e = proc_close($process);
        if( 0 !== $e ){
            $stderr and trigger_error( $stderr, E_USER_WARNING );
            throw new Exception('Java program exited with code '.sprintf('%d',$e) );
        }
        return $stdout; 
    }
    
} 