<?php
namespace evo\debug;

/**
 *
 * (c) 2016 Hugh Durham III
 *
 * For license information please view the LICENSE file included with this source code.
 *
 * Debug class - circular refrence safe
 *
 * @author HughDurham {ArtisticPhoenix}
 * @package Evo
 * @subpackage debug
 *
 */
class Debug{
    /**
     * indicate that our depth limit is reached
     * @var string
     */
    const DEPTH_LIMIT = '#_DEPTH_LIMIT_#';
    
    
    /**
     * indicate a circular refrence
     * @var string
     */
    const CIRCULAR_REFRENCE = '#_CIRCULAR_REFRENCE_#';
    
    /**
     * sprint_f formating patterns
     * @var string
     */
    const TYPE_BOOLEAN      = "bool(%s)";
    const TYPE_INTEGER      = "int(%s)";
    const TYPE_DOUBLE       = "float(%s)";
    const TYPE_STRING       = "string(%s)";
    const TYPE_RESOURCE     = "resource(%s) of type (%s)";
    const TYPE_NULL         = "NULL";
    const TYPE_UNKNOWN      = "UNKNOWN TYPE";
    const TYPE_ARRAY        = "array(%s){%s}";
    const TYPE_ARRAY_ITEM   = "[%s] => %s,";
    const TYPE_OBJECT       = "object(%s)#%s (%s) {%s}";
    const TYPE_PROPERTY     = "[\"%s\":%s] => %s";
    
    /**
     * visibility tags
     * @var string
     */
    const TYPE_CONSTANT     = 'const';
    const TYPE_PUBLIC       = 'public';
    const TYPE_PROTECTED    = 'protected';
    const TYPE_PRIVATE      = 'private';
    const TYPE_STATIC       = 'static';
    
    /**
     * show constants bitwise
     * @var int
     */
    const SHOW_OBJ_CONSTANTS    = 1;
    
    const SHOW_OBJ_PUBLIC       = 2;
    
    const SHOW_OBJ_PROTECTED    = 4;
    
    const SHOW_OBJ_PRIVATE      = 8;
    
    /**
     * public methods and properties
     * @var int
     */
    const SHOW_OBJ_ACCESSIBLE   = 3;
    
    /**
     * public methods and properties,
     * constants, protected methods and protected
     * @var unknown
     */
    const SHOW_OBJ_VISABLE      = 7;
    
    /**
     *
     * @var number
     */
    const SHOW_OBJ_ALL          = 15;
    
    /**
     *
     * @var number
     */
    const DEFAULT_DEPTH         = 10;
    
    /**
     *
     * @var number
     */
    private static $maxDepth;
    
    /**
     *
     * @var bool
     */
    private static $htmlOutput = true;
    
    /**
     * No Construction
     */
    private function __construct()
    {
    }
    
    /**
     * No Cloning!
     */
    private function __clone()
    {
    }
    
    /**
     * set output as HTML for line endings ( br ) etc..
     * @param bool $toHtml
     */
    public static function setHtmlOutput($toHtml = true)
    {
        self::$htmlOutput = $toHtml;
    }
    
    /**
     * echo a debug string
     * @param mixed $var
     * @param number $depthLimit - max depth limit to output
     * @param number $flags - one of the SHOW_* constants
     * @param number $offset - offset the stack trace
     */
    public static function dump($var = null, $depthLimit = self::DEFAULT_DEPTH, $flags = self::SHOW_OBJ_VISABLE, $offset = 0)
    {
        self::$maxDepth  = $depthLimit;
        
        $before = self::$htmlOutput ? '<pre>' : '';
        $after =  self::$htmlOutput ? '</pre>' : '';
        
        $ln = self::indentLine();
        
        echo    $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        self::getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        self::getDebug($var, $flags) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     * return debug as a string
     * @param mixed $var
     * @param number $depthLimit - max depth limit to output
     * @param number $flags - one of the SHOW_* constants
     * @param number $offset - offset the stack trace
     * @return string
     */
    public static function export($var = null, $depthLimit = self::DEFAULT_DEPTH, $flags = self::SHOW_OBJ_VISABLE, $offset = 0)
    {
        self::$maxDepth  = $depthLimit;
        
        $before = self::$htmlOutput ? '<pre>' : '';
        $after =  self::$htmlOutput ? '</pre>' : '';
        
        $ln = self::indentLine();
        
        return    $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        self::getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        self::getDebug($var, $flags) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     * capture output for debugging purposes
     */
    public static function start()
    {
        ob_start();
    }
    
    /**
     * format and ouput captured debugging
     * @param number $offset - offset the stack trace
     */
    public static function end($offset = 0)
    {
        $output = ob_get_clean();
        
        $before = self::$htmlOutput ? '<pre>' : '';
        $after =  self::$htmlOutput ? '</pre>' : '';
        
        $ln = self::indentLine();
        
        echo    $before . str_pad("* ".__METHOD__." *", 90, "*", STR_PAD_BOTH) . $ln .
        self::getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, ".", STR_PAD_BOTH) . $ln .
        $output . $ln .
        str_pad("", 90, "*", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     *
     * @param mixed $var
     * @param number $depthLimit - max depth limit to output
     * @param number $flags - one of the SHOW_* constants
     */
    public static function kill($var = null, $depthLimit = self::DEFAULT_DEPTH, $flags = self::SHOW_OBJ_VISABLE)
    {
        self::$maxDepth  = $depthLimit;
        
        $before = self::$htmlOutput ? '<pre>' : '';
        $after =  self::$htmlOutput ? '</pre>' : '';
        
        $ln = self::indentLine();
        
        echo    $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        self::getTraceFirstAsString(1) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        self::getDebug($var, $flags) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln . $ln . $after;
        exit;
    }
    
    /**
     *
     * @param string $var
     * @param number $flags
     * @param number $level
     * @param array $objInstances
     * @throws \Jet\Exception
     * @return string
     */
    private static function getDebug($var = null, $flags = self::SHOW_OBJ_VISABLE, $level = 0, array $objInstances = array())
    {
        $type = gettype($var);
        $ln = self::indentLine();
        
        switch ($type) {
            case 'boolean':
                return sprintf(self::TYPE_BOOLEAN, $var ? 'true' : 'false');
            case 'integer':
                return sprintf(self::TYPE_INTEGER, $var);
            case 'double':
                $float = (float)$var;
                if (strlen($float) == 1) {
                    $float = number_format($var, 1);
                }
                return sprintf(self::TYPE_DOUBLE, $float);
            case 'string':
                $len = strlen($var);
                if (self::$htmlOutput) {
                    $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8', false);
                }
                return sprintf(self::TYPE_STRING, $len).' "'.$var.'"';
            case 'resource':
            case 'resource (closed)':
                return sprintf(self::TYPE_RESOURCE, intval($var), get_resource_type($var));
            case 'NULL':
                return self::TYPE_NULL;
            case 'unknown type':
                return self::TYPE_UNKNOWN;
            case 'array':
                $output = '';
                $len = count($var);
                
                if ($len > 0) {
                    ++$level;
                    if ($level < self::$maxDepth) {
                        foreach ($var as $k => $v) {
                            if (gettype($k) == 'string') {
                                if (self::$htmlOutput) {
                                    $k = htmlspecialchars($k, ENT_QUOTES, 'UTF-8', false);
                                }
                                $k = '"'.$k.'"';
                            }
                            $_v = self::getDebug($v, $flags, $level, $objInstances); //recursive
                            $output .=  $ln . self::indentLevel($level) . sprintf(self::TYPE_ARRAY_ITEM, $k, $_v);
                        }
                    } else {
                        $output .= $ln . self::indentLevel($level) . self::DEPTH_LIMIT;
                    }
                    --$level;
                    $output .= $ln . self::indentLevel($level);
                }
                return sprintf(self::TYPE_ARRAY, $len, $output);
            case 'object':
                $output = '';
                $class = get_class($var);
                $hash = spl_object_hash($var);
                $prop_count = 0;
                
                if (!isset($objInstances[ $class ])) {
                    $objInstances[ $class ] = array();
                }
                
                if (false === ($index = array_search($hash, $objInstances[ $class ]))) {
                    $index = count($objInstances[ $class ]);
                    $objInstances[ $class ][] = $hash;
                    ++$level;
                    
                    if ($level < self::$maxDepth) {
                        $ReflectionObj = new \ReflectionObject($var);
                        if (self::SHOW_OBJ_CONSTANTS & $flags) {
                            //CONSTANTS
                            foreach ($ReflectionObj->getConstants() as $k => $v) {
                                $output .= $ln . self::indentLevel($level);
                                $output .= sprintf(
                                    self::TYPE_PROPERTY,
                                    $k,
                                    self::TYPE_CONSTANT,
                                    self::getDebug($v, $flags, $level, $objInstances) //recursive
                                    );
                                $output .= ",";
                                ++$prop_count;
                            }
                        }
                        
                        $Properties = $ReflectionObj->getProperties();
                        
                        /* @var $Property \ReflectionProperty */
                        foreach ($Properties as $Property) {
                            //types
                            if (self::SHOW_OBJ_PUBLIC & $flags && $Property->isPublic()) {
                                $prop_type = self::TYPE_PUBLIC;
                            } elseif (self::SHOW_OBJ_PROTECTED & $flags && $Property->isProtected()) {
                                $Property->setAccessible(true);
                                $prop_type = self::TYPE_PROTECTED;
                            } elseif (self::SHOW_OBJ_PRIVATE & $flags && $Property->isPrivate()) {
                                $Property->setAccessible(true);
                                $prop_type = self::TYPE_PRIVATE;
                            } else {
                                continue;
                            }
                            $k = $Property->getName();
                            $v = $Property->getValue($var);
                            
                            //static
                            if ($Property->isStatic()) {
                                $prop_type .= ' '.self::TYPE_STATIC;
                            }
                            
                            
                            $output .= $ln . self::indentLevel($level);
                            $output .= sprintf(
                                self::TYPE_PROPERTY,
                                $k,
                                $prop_type,
                                self::getDebug($v, $flags, $level, $objInstances) //recurse
                                );
                            $output .= ",";
                            ++$prop_count;
                        }
                    } else {
                        $output .= $ln . self::indentLevel($level) . self::DEPTH_LIMIT;
                    }
                    --$level;
                    
                    if (!empty($output)) {
                        $output .= $ln . self::indentLevel($level);
                    }
                } else {
                    $output .= self::CIRCULAR_REFRENCE;
                }
                
                return sprintf(
                    self::TYPE_OBJECT,
                    $class,
                    $index,
                    $prop_count,
                    $output
                    );
        }
        
        //throw new \Jet\Exception('', \Jet\Exception::NOT_YET_IMPLIMENTED);
    }
    
    /**
     *
     */
    private static function indentLine()
    {
        if (self::$htmlOutput) {
            return "<br>";
        } else {
            return PHP_EOL;
        }
    }
    
    /**
     *
     * @param number $level
     */
    private static function indentLevel($level)
    {
        if (self::$htmlOutput) {
            return str_repeat("&nbsp;", $level * 5);
        } else {
            return str_repeat("\t", $level);
        }
    }
    
    /**
     * get the line this file was called on ( +1 )
     * @param number $offset
     * @return array
     */
    public static function trace($offset = 0)
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
     * get a backtrace - formatted as a stacktrace
     * @param number $offset
     */
    public static function backTrace($offset = 0)
    {
        $trace = self::trace($offset);
        
        $first = array_shift($trace);
        
        $before = self::$htmlOutput ? '<pre>' : '';
        $after =  self::$htmlOutput ? '</pre>' : '';
        $ln = self::indentLine();
        
        $str_trace = $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln;
        $str_trace .= "Output from FILE[ {$first['file']} ] on LINE[ {$first['line']} ]" . $ln;
        $str_trace .= str_pad("", 90, "-", STR_PAD_BOTH) . $ln;
        
        $k = -1;
        foreach ($trace as $k => $v) {
            $str_trace .= "#{$k} ";
            $str_trace .= isset($v['file']) ? $v['file'] : '';
            $str_trace .= isset($v['line']) ? '('.$v['line'].'): ' : '';
            
            $str_trace .= isset($v['class']) ? $v['class'].$v['type'] : '';
            
            $function = isset($v['function']) ? $v['function'].'(%s)' : '';
            if ($function == '{closure}(%s)') {
                $function = '[internal function]: {closure}(%s)';
            }
            
            $args = array();
            if (isset($v['args'])) {
                foreach ($v['args'] as $w) {
                    $o = '';
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
        
        $str_trace .= str_pad("", 90, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
        
        return $str_trace;
    }
    
    /**
     *
     * @param number $offset
     * @return string
     */
    public static function getTraceFirstAsString($offset = 0)
    {
        $trace = self::getTraceFirst($offset);
        return "Output from FILE[ {$trace['file']} ] on LINE[ {$trace['line']} ]";
    }
    
    /**
     *
     * @param number $offset
     * @return array
     */
    public static function getTraceFirst($offset = 0)
    {
        $trace = self::trace($offset);
        return reset($trace);
    }   
}