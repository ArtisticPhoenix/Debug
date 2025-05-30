<?php
namespace evo\debug;

use evo\pattern\singleton\MultitonTrait;
use evo\pattern\singleton\MultitonInterface;
use ReflectionClassConstant;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionException;
use ReflectionObject;
use Throwable;
use UnitEnum;

/**
 * test
 *
 * (c) 2016 Hugh Durham III
 *
 * For license information please view the LICENSE file included with this source code.
 *
 * Debug class - circular reference safe
 *
 * @author HughDurham {ArtisticPhoenix}
 * @package Evo
 * @subpackage debug
 *
 */
class Debug implements MultitonInterface
{
    use MultitonTrait;
    
    /**
     * The alias used when registering functions
     *
     * @var string
     */
    const string ALIAS_FUNCTIONS = 'functions';
    
    
    /**
     * show constants bitwise
     * @var int
     */
    const int SHOW_CONSTANTS = 1;
    
    /**
     * show public properties bitwise
     * @var int
     */
    const int SHOW_PUBLIC = 2;
    
    /**
     * show protected properties bitwise
     * @var int
     */
    const int SHOW_PROTECTED = 4;
    
    /**
     * show private properties bitwise
     * @var int
     */
    const int SHOW_PRIVATE = 8;
    
    /**
     * show constants and public properties
     * @var int
     */
    const int SHOW_ACCESSIBLE = 3;
    
    /**
     * show constants and public/protected properties
     * @var int
     */
    const int SHOW_VISIBLE = 7;
    
    /**
     * show constants and public/protected properties
     * @var int
     */
    const int SHOW_ALL = 15;
  
    /**
     *
     * @var string
     */
    protected static string $NULL = 'null';
    
    /**
     *
     * @var string
     */
    protected static string $PUBLIC = 'public';
    
    /**
     *
     * @var string
     */
    protected static string $PROTECTED = 'protected';
    
    /**
     *
     * @var string
     */
    protected static string $PRIVATE = 'private';
    
    /**
     *
     * @var string
     */
    protected static string $CONSTANT = 'constant';

    /**
     *
     * @var string
     */
    protected static string $READONLY = 'readonly';

    /**
     *
     * @var string
     */
    protected static string $ABSTRACT = 'abstract';

    /**
     *
     * @var string
     */
    protected static string $FINAL = 'final';
    
    /**
     *
     * @var string
     */
    protected static string $STATIC = 'static';
    
    /**
     *
     * @var string
     */
    protected static string $DEPTH_LIMIT = '~DEPTH_LIMIT~';
    
    /**
     * REFERENCE
     * @var string
     */
    protected static string $CIRCULAR_REFERENCE = '~CIRCULAR_REFERENCE~';

    /**
     * REFERENCE
     * @var string
     */
    protected static string $UNINITIALIZED  = '~UNINITIALIZED~';

    /**
     * formatting templates
     * @var array
     */
    protected array $templates = [
        'boolean'               => 'bool(%s)',
        'integer'               => 'int(%s)',
        'double'                => 'float(%s)',
        'string'                => 'string(%s) "%s"',
        'resource'              => 'resource(%s) of type (%s)',
        'unknown type'          => 'unknown(%s)',
        'array'                 => 'array(%s){%s}',
        'array item'            => '[%s] => %s,',
        'object'                => 'object(%s)#%s (%s) {%s}',
        'property'              => '["%s":%s] => %s',
        'enum'                  => 'enum(%s:%s)#%s (%s) {%s}',
        'backed_case'           => '["%s":case] => %s',
        'case'                  => '["%s":case]',
    ];
    
    /**
     * output as html
     * @var boolean
     */
    protected bool $htmlOutput = false;
    
    /**
     * depth limit for nested data
     * @var int
     */
    protected int $depthLimit = 10;
    
    /**
    * line length of messages
    * @var int
    */
    protected int $messageWidth = 78;

    /**
     * Bitwise flags currently set
     * @var int
     */
    protected int $flags = self::SHOW_ALL;
    
    /**
     * @var string
     */
    protected string $buffered = '';
    
    /**
     * Regester procedural functions, aka functional wrappers for the debug class.
     *
     * use the ALIAS_FUNCTIONS class constant to access this instance of Debug
     * @example <pre>
     * Change the output to HTML
     * Debug::getInstance(Debug::ALIAS_FUNCTIONS)->setHtmlOutput(true);
     * Set the Depth limit
     * Debug::getInstance(Debug::ALIAS_FUNCTIONS)->setDepthLimit(5);
     */
    public static function regesterFunctions(): void
    {
        //scope resolution
        $load = static function () {
            require_once __DIR__ . DIRECTORY_SEPARATOR . 'functions.php';
        };
        $load();
    }

    
    //===================== Getters/Setters ===============
    /**
     *
     * @return bool
     */
    public function getHtmlOutput(): bool
    {
        return $this->htmlOutput;
    }
    
    /**
     *
     * @param bool $htmlOutput
     * @return void
     */
    public function setHtmlOutput(bool $htmlOutput): void
    {
        $this->htmlOutput = $htmlOutput;
    }
    
    /**
     *
     * @return int
     */
    public function getDepthLimit(): int
    {
        return $this->depthLimit;
    }
    
    /**
     *
     * @param int $depthLimit
     * @return void
     */
    public function setDepthLimit(int $depthLimit): void
    {
        $this->depthLimit = $depthLimit;
    }
    
    /**
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }
    
    /**
     * Set bitwise Flag, one or more of the SHOW_* constants
     *
     * @param int $flags
     * @return void
     *
     */
    public function setFlags(int $flags): void
    {
        $this->flags = $flags;
    }
    
    /**
     * Return the max with for messages in chars
     * @return int
     */
    public function getMessageWidth() : int
    {
        return $this->messageWidth;
    }
    
    /**
     * only applies to the message wrapper
     *
     * @param int $width
     * @return void
     */
    public function setMessageWidth(int $width): void
    {
        $this->messageWidth = $width;
    }
    
    //===================== Main ===============

    /**
     *
     * Print out debug for input.
     *
     * @param mixed $input
     * @param int $offset
     *
     * @return void
     */
    public function dump(mixed $input=null, int $offset = 0): void
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';

        $ln = $this->indentLine();

        echo $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln .
            $this->getTraceFirstAsString($offset) . $ln .
            str_pad("", $this->messageWidth, "-", STR_PAD_BOTH) . $ln .
            $this->varExport($input) . $ln .
            str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }

    /**
     * Print out debug for an exception.
     *
     * @param Throwable $exception
     * @param int $offset
     * @return void
     */
    public function dumpException(Throwable $exception, int $offset = 0): void
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';

        $ln = $this->indentLine();
        $message = get_class($exception)."::{$exception->getCode()} {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}\n{$exception->getTraceAsString()}";

        echo $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln .
            $this->getTraceFirstAsString($offset) . $ln .
            str_pad("", $this->messageWidth, "- ", STR_PAD_BOTH) . $ln .
            $message . $ln .
            str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }

    /**
     *
     * Processes and outputs the SQL query with substituted parameters for debugging purposes (only).
     *
     * @param string $statement The SQL query string containing placeholders.
     * @param array $params An associative or indexed array of parameters to replace placeholders in the query.
     * @param int $offset The offset value used for any specific tracing/debugging purposes.
     * @return void
     */
    public function dumpSql(string $statement, array $params=[], int $offset = 0): void
    {
        //replace actual NULL values with a string 'NULL'
        $params = array_map(fn($i)=>null===$i?'NULL':$i, $params);

        if(str_contains($statement, '?')){
            $statement = sprintf(str_replace('?', '%s', $statement), ...$params);
        }else if(str_contains($statement, ':')){
            //normalize the array keys
            $placeholders = preg_filter('/^:?/', ':', array_keys($params));
            $statement = str_replace($placeholders, $params, $statement);
        }

        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';

        $ln = $this->indentLine();

        echo $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln .
            $this->getTraceFirstAsString($offset) . $ln .
            str_pad("", $this->messageWidth, "-", STR_PAD_BOTH) . $ln .
            $statement . $ln .
            str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
        
    }

    /**
     *
     * return debug from an input
     *
     * @param mixed $input
     * @param int $offset
     * @return string
     */
    public function export(mixed $input=null, int $offset = 0): string
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        return $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln .
            $this->getTraceFirstAsString($offset) . $ln .
            str_pad("", $this->messageWidth, "-", STR_PAD_BOTH) . $ln .
            $this->varExport($input) . $ln .
            str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     * Start debug output buffer
     *
     * @param int $offset
     * @return void
     */
    public function start(int $offset=0): void
    {
        $this->buffered=$this->getTraceFirstAsString($offset);
        ob_start();
    }
    
    /**
     * flush the debug buffer to output
     *
     * @param mixed $offset
     * @return void
     */
    public function flush(int $offset=0): void
    {
        echo $this->end($offset);
    }
    
    /**
     * end and return debug buffer data
     *
     * @param int $offset
     * @return string
     */
    public function end(int $offset=0): string
    {
        $output = ob_get_clean();
        
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        $buffer = $before . str_pad("* ".__CLASS__."::start *", $this->messageWidth, "*", STR_PAD_BOTH) . $ln .
        $this->buffered . $ln .
        str_pad("", $this->messageWidth, ".", STR_PAD_BOTH) . $ln .
        $output . $ln .
        str_pad("", $this->messageWidth, ".", STR_PAD_BOTH) . $ln .
        $this->getTraceFirstAsString($offset) . $ln .
        str_pad(" ".__METHOD__." ", $this->messageWidth, "*", STR_PAD_BOTH) . $ln  . $ln . $after;
        
        $this->buffered = '';
        
        return $buffer;
    }

    /**
     * exit PHP and print a debug message
     *
     * @param mixed $input
     * @param int $offset
     */
    public function kill(mixed $input=null, int $offset=0) : never
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';

        $ln = $this->indentLine();

        echo $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln .
            $this->getTraceFirstAsString($offset) . $ln .
            str_pad("", $this->messageWidth, "-", STR_PAD_BOTH) . $ln .
            $this->varExport($input) . $ln .
            str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln . $ln . $after;
        exit;
    }

    /**
     *
     * output the dump for a variable (no outer formatting)
     *
     * @param mixed $input
     */
    public function varDump(mixed $input): void
    {
        echo $this->varExport($input);
    }

    /**
     * return the dump for a variable (no outer formatting)
     *
     * @param mixed $input
     * @param int $level - current depth level [internal use]
     * @param array $objInstances - map of current object instance [internal]
     * @return string
     */
    public function varExport(mixed $input, int $level=0, array $objInstances=array()): string
    {
        $type = gettype($input);
        $ln = $this->indentLine();
        
        switch ($type) {
            case 'boolean':
                $ob_value = $input ? 'true' : 'false';
                return $this->templateVar($type, $ob_value);
            case 'double':
                $float = (float)$input;
                if (strlen($float) == 1 || (strlen($float) == 2 && $float < 0)) {
                    $float = number_format($input, 1);
                }
                return $this->templateVar($type, $float);
            case 'string':
                $len = strlen($input);
                if ($this->htmlOutput) {
                    $input = htmlspecialchars($input, ENT_NOQUOTES, 'UTF-8', false);
                }
                $input = addslashes($input);
                
                return $this->templateVar($type, $len, $input);
            case 'resource':
            case 'resource (closed)':
                return sprintf($this->templates['resource'], intval($input), get_resource_type($input));
            case 'NULL':
                return self::$NULL;
            case 'array':
                $output = '';
                $len = count($input);

                if ($len > 0) {
                    ++$level;
                    if ($level < $this->depthLimit) {
                        foreach ($input as $ob_name => $ob_value) {
                            //HTML escape keys
                            if (gettype($ob_name) == 'string') {
                                if ($this->htmlOutput) {
                                    $ob_name = htmlspecialchars($ob_name, ENT_NOQUOTES, 'UTF-8', false);
                                }
                                $ob_name = '"'.$ob_name.'"';
                            }
                            $_v = $this->varExport($ob_value, $level, $objInstances); //recursive
                            $output .=  $ln . $this->indentLevel($level) . $this->templateVar('array item', $ob_name, $_v);
                        }
                    } else {
                        $output .= $ln . $this->indentLevel($level) . self::$DEPTH_LIMIT;
                    }
                    --$level;
                    $output .= $ln . $this->indentLevel($level);
                }
                return $this->templateVar($type, $len, $output);
            case 'object':
                $prefix = '';
                $output = '';
                $ob_class = get_class($input);
                $ob_hash = spl_object_hash($input);

                if (!isset($objInstances[$ob_class])) {
                    //register the object to our circular reference cache
                    $objInstances[$ob_class] = array();
                }

                if (!in_array($ob_hash, $objInstances[$ob_class])) {
                    $ob_index = count($objInstances[$ob_class]); //fake the index for objects not already cached
                    $objInstances[$ob_class][] = $ob_hash;
                    ++$level;

                    if ($level < $this->depthLimit) {
                        if($input instanceof UnitEnum) {
                            //Enumerators
                            $type = 'enum';
                            $name = $input->name;

                            try {
                                $ReflectionEnum = new ReflectionEnum($input);
                            }catch(ReflectionException $e){
                                return get_class($e).'::'.$e->getCode().' '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine();
                            }

                            if ($this->hasFlag(self::SHOW_CONSTANTS)) {
                                //CONSTANTS ["%s":%s] => %s
                                foreach ($ReflectionEnum->getConstants() as $const_name => $const_value) {
                                    if(is_object($const_value)){
                                        $ReflectionConst = new ReflectionClassConstant(get_class($const_value), $const_name);
                                        if($ReflectionConst->isEnumCase())
                                            continue;
                                    }

                                    $output .= $ln . $this->indentLevel($level);
                                    $output .= $this->templateVar(
                                        'property',
                                        $const_name,
                                        self::$CONSTANT,
                                        $this->varExport($const_value, $level, $objInstances) //recursive
                                    );
                                    $output .= ",";
                                }
                            }

                            $case_count = 0;
                            $Cases = $ReflectionEnum->getCases();
                            foreach ($Cases as $Case) {
                                //["%s"] => %s | ["%s"]
                                $case_name = $Case->name;
                                $output .= $ln . $this->indentLevel($level);
                                if($Case instanceof ReflectionEnumBackedCase){
                                    $output .= $this->templateVar(
                                        'backed_case',
                                        $case_name,
                                        $this->varExport($Case->getValue()->value)
                                    );
                                }else{
                                    $output .= $this->templateVar('case', $case_name);
                                }
                                $output .= ",";
                                ++$case_count;
                            }//end foreach

                             //enum(%s:%s)#%s (%s) {%s}
                             $args = [
                                 $ob_class,
                                 $name,
                                 $ob_index,
                                 $case_count
                             ];
                        }else {
                            //typical objects
                            $ReflectionObj = new ReflectionObject($input);

                            $final = $ReflectionObj->isFinal() ? self::$FINAL : '';
                            $abstract = $ReflectionObj->isAbstract() ?  self::$ABSTRACT : '';
                            $readonly = $ReflectionObj->isReadOnly() ? self::$READONLY : '';

                            $prefix = implode(' ', array_filter([$final, $abstract, $readonly]));

                            if ($this->hasFlag(self::SHOW_CONSTANTS)) {
                                //CONSTANTS
                                foreach ($ReflectionObj->getConstants() as $const_name => $const_value) {
                                    //["%s":%s] => %s
                                    $output .= $ln . $this->indentLevel($level);
                                    $output .= $this->templateVar(
                                        'property',
                                        $const_name,
                                        self::$CONSTANT,
                                        $this->varExport($const_value, $level, $objInstances) //recursive
                                    );
                                    $output .= ",";
                                }
                            }

                            $prop_count = 0;
                            $Properties = $ReflectionObj->getProperties();

                            foreach ($Properties as $Property) {
                                //["%s":%s] => %s
                                $prop_type = [];

                                if ($this->hasFlag(self::SHOW_PUBLIC) && $Property->isPublic()) {
                                    $prop_type[] = self::$PUBLIC;
                                } elseif ($this->hasFlag(self::SHOW_PROTECTED) && $Property->isProtected()) {
                                    //$Property->setAccessible(true);
                                    $prop_type[] = self::$PROTECTED;
                                } elseif ($this->hasFlag(self::SHOW_PRIVATE) && $Property->isPrivate()) {
                                    //$Property->setAccessible(true);
                                    $prop_type[] = self::$PRIVATE;
                                } else {
                                    continue;
                                }

                                //static
                                if ($Property->isStatic()) {
                                    $prop_type[] = self::$STATIC;
                                }

                                $ob_name = $Property->getName();
                                //read only
                                if($Property->isReadOnly() && !$Property->isInitialized($input)){
                                    $ob_value = self::$UNINITIALIZED;
                                }else{
                                    $ob_value = $Property->getValue($input);
                                }

                                if($Property->isReadOnly()){
                                    $prop_type[] = self::$READONLY;
                                }

                                $output .= $ln . $this->indentLevel($level);
                                $output .= $this->templateVar(
                                    'property',
                                    $ob_name,
                                    implode(' ',$prop_type),
                                    $this->varExport($ob_value, $level, $objInstances) //recurse
                                );
                                $output .= ",";
                                ++$prop_count;
                            }//end foreach

                            //object(%s)#%s (%s) {%s}
                            $args = [
                                $ob_class,
                                $ob_index,
                                $prop_count
                            ];
                        } //end if ENUM
                    } else {
                        //if depth limit is reached
                        $args = [
                            $ob_class,
                            false,
                            0
                        ];
                        //object(%s)#%s (%s) {%s}
                        $output .= $ln . $this->indentLevel($level) . self::$DEPTH_LIMIT;
                    } //end if depth limit

                    --$level;
                    if (!empty($output)) {
                        $output = rtrim($output, ',' ) . $ln . $this->indentLevel($level);
                    }
                } else {
                    //CIRCULAR_REFERENCE
                    $args = [
                        $ob_class,
                        false,
                        0
                    ];
                    $output .= self::$CIRCULAR_REFERENCE;
                }
                $args[] = $output;
                return $prefix . $this->templateVar($type, ...$args);
            case 'integer':
            case 'unknown type':
            default:
                return $this->templateVar($type, $input);
        } //end switch
    }

    //===================== Helpers ===============
    /**
     *
     * @param string $type
     * @param mixed ...$args
     * @return string
     */
    protected function templateVar(string $type, mixed ...$args): string
    {
        return sprintf($this->templates[$type], ...$args);
    }
    
    /**
     * check if a flag is set
     *
     * @param int $flag
     * @return bool
     */
    public function hasFlag(int $flag): bool
    {
        return $this->flags & $flag;
    }
    
    /**
     * add a new line
     * @return string
     */
    protected function indentLine(): string
    {
        return PHP_EOL;
    }
    
    /**
     * add a tab
     * @param int $level
     * @return string
     */
    protected function indentLevel(int $level): string
    {
        return str_repeat("\t", $level);
    }
    
    /**
     * return the part of the backtrace where this function was called from
     * @param int $offset
     * @return array
     */
    public function trace(int $offset=0): array
    {
        $trace = debug_backtrace(false);
        foreach ($trace as $t) {
            if ($t['file'] != __FILE__) {
                break;
            }
            
            ++$offset;
        }
        return array_slice($trace, ($offset - count($trace)));
    }
    
    /**
     * print a backtrace - formatted as a stacktrace
     * @param int $offset
     */
    public function backTrace(int $offset=0): void
    {
        $trace = $this->trace($offset);

        $first = array_shift($trace);
        
        $before = $this->htmlOutput ? '<pre>' : '';
        $after =  $this->htmlOutput ? '</pre>' : '';
        $ln = $this->indentLine();
        
        $str_trace = $before . str_pad("= ".__METHOD__." =", $this->messageWidth, "=", STR_PAD_BOTH) . $ln;
        $str_trace .= "Output from FILE[ {$first['file']} ] on LINE[ {$first['line']} ]" . $ln;
        $str_trace .= str_pad("", $this->messageWidth, "-", STR_PAD_BOTH) . $ln;

        $k = -1;
        foreach ($trace as $k => $v) {
            $str_trace .= "#$k ";
            $str_trace .= $v['file'] ?? '';
            $str_trace .= isset($v['line']) ? '('.$v['line'].'): ' : '';
            
            $str_trace .= isset($v['class']) ? $v['class'].$v['type'] : '';
            
            $function = isset($v['function']) ? $v['function'].'(%s)' : '';
            if ($function == '{closure}(%s)') {
                $function = '[internal function]: {closure}(%s)';
            }
            
            $args = array();
            if (isset($v['args'])) {
                foreach ($v['args'] as $w) {
                    if (is_object($w)) {
                        $o = 'Object('.get_class($w).')';
                    } elseif (is_array($w)) {
                        $o = 'Array';
                    } else {
                        $o = (string)($w);
                        $o = (strlen($o) > 17) ? "'".substr($o, 0, 15)."...'" : $o;
                    }
                    $args[] =  $o;
                }
                $str_trace .= sprintf($function, implode(', ', $args));
            }
            $str_trace .= $ln;
        }
        
        $str_trace .= "#".($k+1)." {main}".$ln;
        
        $str_trace .= str_pad("", $this->messageWidth, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
        
        echo $str_trace;
    }
    
    /**
     * return the part of the stacktrace where the call was made from
     *
     * @param int $offset
     * @return array
     */
    public function getTraceFirst(int $offset=0): array
    {
        $trace = $this->trace($offset);
        return reset($trace);
    }
    
    /**
     * return the formatted trace of the first line.
     *
     * @param int $offset
     * @return string
     */
    public function getTraceFirstAsString(int $offset=0): string
    {
        $trace = $this->getTraceFirst($offset);
        return "Output from FILE[ {$trace['file']} ] on LINE[ {$trace['line']} ]";
    }
}
