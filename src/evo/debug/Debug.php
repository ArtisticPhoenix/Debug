<?php
namespace evo\debug;

use evo\pattern\singleton\MultitonTrait;
use evo\pattern\singleton\MultitonInterface;

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
class Debug implements MultitonInterface
{
    use MultitonTrait;
    /**
     * show constants bitwise
     * @var int
     */
    const SHOW_CONSTANTS = 1;
    
    /**
     * show public properties bitwise
     * @var int
     */
    const SHOW_PUBLIC = 2;
    
    /**
     * show protected properties bitwise
     * @var int
     */
    const SHOW_PROTECTED = 4;
    
    /**
     * show private properties bitwise
     * @var int
     */
    const SHOW_PRIVATE = 8;
    
    /**
     * show constants and public properties
     * @var int
     */
    const SHOW_ACCESSIBLE = 3;
    
    /**
     * show constants and public/protected propertiesd
     * @var int
     */
    const SHOW_VISABLE = 7;
    
    /**
     * show constants and public/protected propertiesd
     * @var int
     */
    const SHOW_ALL = 15;

    /**
     *
     * @var string
     */
    protected static $NULL = 'null';
    
    /**
     *
     * @var string
     */
    protected static $PUBLIC = 'public';
    
    /**
     *
     * @var string
     */
    protected static $PROTECTED = 'protected';
    
    /**
     *
     * @var string
     */
    protected static $PRIVATE = 'private';
    
    /**
     *
     * @var string
     */
    protected static $CONSTANT = 'constant';
    
    /**
     *
     * @var string
     */
    protected static $STATIC = 'static';
    
    /**
     *
     * @var string
     */
    protected static $DEPTH_LIMIT = '~DEPTH_LIMIT~';
    
    /**
     *
     * @var string
     */
    protected static $CIRCULAR_REFRENCE = '~CIRCULAR_REFRENCE~';
  
    /**
     * formatting templates
     * @var array
     */
    protected $templates = [
        'boolean'           => 'bool(%s)',
        'integer'           => 'int(%s)',
        'double'            => 'float(%s)',
        'string'            => 'string(%s) "%s"',
        'resource'          => 'resource(%s) of type (%s)',
        'unknown type'           => 'unknown(%s)',
        'array'             => 'array(%s){%s}',
        'array item'        => '[%s] => %s,',
        'object'            => 'object(%s)#%s (%s) {%s}',
        'property'          => '["%s":%s] => %s',
    ];
    
    /**
     * output as html
     * @var boolean
     */
    protected $htmlOutput = false;
    
    /**
     * depth limit for nested data
     * @var int
     */
    protected $depthLimit = 10;

    /**
     * Bitwise flags currently set
     * @var int
     */
    protected $flags = self::SHOW_ALL;
    
    /**
     * @var int
     */
    protected $buffered = '';
    
    /**
     * Regester procedural functions, aka functional wrappers for the debug class.
     */
    public function regesterFunctions()
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
     * @return boolean
     */
    public function getHtmlOutput()
    {
        return $this->htmlOutput;
    }
    
    /**
     *
     * @param string $toHtml
     */
    public function setHtmlOutput($htmlOutput)
    {
        $this->htmlOutput = $htmlOutput;
    }
    
    /**
     *
     * @return int
     */
    public function getDepthLimit()
    {
        return $this->depthLimit;
    }
    
    /**
     *
     * @param number $depthLimit
     */
    public function setDepthLimit($depthLimit)
    {
        $this->depthLimit = $depthLimit;
    }
    
    /**
     *
     * @return int
     */
    public function getFlags()
    {
        return $this->flags;
    }
    
    /**
     * Set bitwise Flag, one or more of the SHOW_* constants
     *
     * @param int $flags
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;
    }
    
    //===================== Main ===============
    /**
     *
     * Print out debug for input.
     *
     * @param mixed $input
     * @param int $offset
     */
    public function dump($input, $offset = 0)
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        echo $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        $this->getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        $this->varExport($input) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     *
     * return debug fro an input
     *
     * @param mixed $input
     * @param int $offset
     * @return string
     */
    public function export($input, $offset = 0)
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        return $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        $this->getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        $this->varExport($input) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln  . $ln . $after;
    }
    
    /**
     * Start debug output buffer
     *
     * @param mixed $input
     * @param int $offset
     */
    public function start($offset = 0)
    {
        $this->buffered = $this->getTraceFirstAsString($offset);
        ob_start();
    }
    
    /**
     * flush the debug buffer to output
     *
     * @param mixed $offset
     */
    public function flush($offset = 0)
    {
        echo $this->end($offset);
    }
    
    /**
     * end and return debug buffer data
     *
     * @param mixed $input
     * @param int $offset
     * @return string
     */
    public function end($offset = 0)
    {
        $output = ob_get_clean();
        
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        $buffer = $before . str_pad("* ".__CLASS__."::start *", 90, "*", STR_PAD_BOTH) . $ln .
        $this->buffered . $ln .
        str_pad("", 90, ".", STR_PAD_BOTH) . $ln .
        $output . $ln .
        str_pad("", 90, ".", STR_PAD_BOTH) . $ln .
        $this->getTraceFirstAsString($offset) . $ln .
        str_pad(" ".__METHOD__." ", 90, "*", STR_PAD_BOTH) . $ln  . $ln . $after;
        
        $this->buffered = '';
        
        return $buffer;
    }
    
    /**
     * exit PHP and pring a debug message
     *
     * @param mixed $input
     * @param int $offset
     */
    public function kill($input, $offset = 0)
    {
        $before = $this->htmlOutput ? '<pre>' : '';
        $after = $this->htmlOutput ? '</pre>' : '';
        
        $ln = $this->indentLine();
        
        echo $before . str_pad("= ".__METHOD__." =", 90, "=", STR_PAD_BOTH) . $ln .
        $this->getTraceFirstAsString($offset) . $ln .
        str_pad("", 90, "-", STR_PAD_BOTH) . $ln .
        $this->varExport($input) . $ln .
        str_pad("", 90, "=", STR_PAD_BOTH) . $ln . $ln . $after;
        exit;
    }
    
    /**
     *
     * output the dump for a variable (no outer formatting)
     *
     * @param mixed $input
     */
    public function varDump($input)
    {
        echo $this->varExport($input);
    }
    
    /**
     * return the dump for a variable (no outer formatting)
     *
     * @param mixed $input
     * @param int $level - current depth level [interal use]
     * @param array $objInstances - map of current object instance [internal]
     * @return string
     */
    public function varExport($input, $level = 0, array $objInstances = array())
    {
        $type = gettype($input);
        $ln = $this->indentLine();
        
        switch ($type) {
            case 'boolean':
                $v = $input ? 'true' : 'false';
                return $this->templateVar($type, $v);
            case 'integer':
                return $this->templateVar($type, $input);
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
                        foreach ($input as $k => $v) {
                            //HTML escape keys
                            if (gettype($k) == 'string') {
                                if ($this->htmlOutput) {
                                    $k = htmlspecialchars($k, ENT_NOQUOTES, 'UTF-8', false);
                                }
                                $k = '"'.$k.'"';
                            }
                            $_v = $this->varExport($v, $level, $objInstances); //recursive
                            $output .=  $ln . $this->indentLevel($level) . $this->templateVar('array item', $k, $_v);
                        }
                    } else {
                        $output .= $ln . $this->indentLevel($level) . self::$DEPTH_LIMIT;
                    }
                    --$level;
                    $output .= $ln . $this->indentLevel($level);
                }
                return $this->templateVar($type, $len, $output);
            case 'object':
                $output = '';
                $class = get_class($input);
                $hash = spl_object_hash($input);
                $prop_count = 0;
                
                if (!isset($objInstances[ $class ])) {
                    $objInstances[ $class ] = array();
                }
                
                if (false === ($index = array_search($hash, $objInstances[ $class ]))) {
                    $index = count($objInstances[ $class ]);
                    $objInstances[ $class ][] = $hash;
                    ++$level;
                    
                    if ($level < $this->depthLimit) {
                        $ReflectionObj = new \ReflectionObject($input);
                        if ($this->hasFlag(self::SHOW_CONSTANTS)) {
                            //CONSTANTS
                            foreach ($ReflectionObj->getConstants() as $k => $v) {
                                $output .= $ln . $this->indentLevel($level);
                                $output .= $this->templateVar(
                                    'property',
                                    $k,
                                    self::$CONSTANT,
                                    $this->varExport($v, $level, $objInstances) //recursive
                                );
                                $output .= ",";
                                ++$prop_count;
                            }
                        }
                        
                        $Properties = $ReflectionObj->getProperties();

                        /* @var $Property \ReflectionProperty */
                        foreach ($Properties as $Property) {
                            //types
                            if ($this->hasFlag(self::SHOW_PUBLIC) && $Property->isPublic()) {
                                $prop_type = self::$PUBLIC;
                            } elseif ($this->hasFlag(self::SHOW_PROTECTED) && $Property->isProtected()) {
                                $Property->setAccessible(true);
                                $prop_type = self::$PROTECTED;
                            } elseif ($this->hasFlag(self::SHOW_PRIVATE) && $Property->isPrivate()) {
                                $Property->setAccessible(true);
                                $prop_type = self::$PRIVATE;
                            } else {
                                continue;
                            }
                            $k = $Property->getName();
                            $v = $Property->getValue($input);
                            
                            //static
                            if ($Property->isStatic()) {
                                $prop_type .= ' '.self::$PRIVATE;
                            }

                            $output .= $ln . $this->indentLevel($level);
                            $output .= $this->templateVar(
                                    'property',
                                    $k,
                                    $prop_type,
                                    $this->varExport($v, $level, $objInstances) //recurse
                            );
                            $output .= ",";
                            ++$prop_count;
                        }
                    } else {
                        $output .= $ln . $this->indentLevel($level) . self::$DEPTH_LIMIT;
                    }
                    --$level;
                    
                    if (!empty($output)) {
                        $output .= $ln . $this->indentLevel($level);
                    }
                } else {
                    $output .= self::$CIRCULAR_REFRENCE;
                }
                
                return $this->templateVar(
                    $type,
                    $class,
                    $index,
                    $prop_count,
                    $output
                );
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
    public function templateVar($type, ...$args)
    {
        return sprintf($this->templates[$type], ...$args);
    }
    
    /**
     * check if a flag is set
     *
     * @param int $flag
     * @return bool
     */
    public function hasFlag($flag)
    {
        return $this->flags & $flag;
    }
    
    /**
     * add a new line
     * @return string
     */
    protected function indentLine()
    {
        if ($this->htmlOutput) {
            return "<br>";
        } else {
            return PHP_EOL;
        }
    }
    
    /**
     * add a tab
     * @param int $level
     * @return string
     */
    protected function indentLevel($level)
    {
        if ($this->htmlOutput) {
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
    public function trace($offset = 0)
    {
        $trace = debug_backtrace(false);
        foreach ($trace as $t) {
            // print_r($t);
            // print_r($offset);
            if ($t['file'] != __FILE__) {
                break;
            }
            
            ++$offset;
        }
        $arr = array_slice($trace, ($offset - count($trace)));
        return $arr;
    }
    
    /**
     * print a backtrace - formatted as a stacktrace
     * @param int $offset
     */
    public function backTrace($offset = 0)
    {
        $trace = $this->trace($offset);

        $first = array_shift($trace);
        
        $before = $this->htmlOutput ? '<pre>' : '';
        $after =  $this->htmlOutput ? '</pre>' : '';
        $ln = $this->indentLine();
        
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
        
        echo $str_trace;
    }
    
    /**
     * return the part of the stacktrace where the call was made from
     *
     * @param number $offset
     * @return array
     */
    public function getTraceFirst($offset = 0)
    {
        $trace = $this->trace($offset);
        return reset($trace);
    }
    
    /**
     * return the formatted trace of the first line.
     *
     * @param number $offset
     * @return string
     */
    public function getTraceFirstAsString($offset = 0)
    {
        $trace = $this->getTraceFirst($offset);
        return "Output from FILE[ {$trace['file']} ] on LINE[ {$trace['line']} ]";
    }
}
