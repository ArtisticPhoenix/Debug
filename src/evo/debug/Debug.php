<?php
namespace evo\debug;

use phpDocumentor\Reflection\Types\Resource;

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
class Debug
{
    
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
        'unknown'           => 'unknown(%s)',
        'array'             => 'array(%s){%s}',
        'array_item'        => '[%s] => %s,',
        'object'            => 'object(%s)#%s (%s) {%s}',
        'property'          => '["%s":%s] => %s',
    ];
    
    /**
     * output as html
     * @var boolean
     */
    protected $htmlOutput;
    
    /**
     * depth limit for nested data
     * @var int
     */
    protected $depthLimit;

    /**
     * Bitwise flags currently set
     * @var int
     */
    protected $flags;
    
    /**
     *
     * @param string $html
     * @param int $depthLimit
     * @param int $flags - one or more of the SHOW_* constants
     */
    public function __construct($htmlOutput = true, $depthLimit = 10, $flags = self::SHOW_ALL)
    {
        $this->setHtmlOutput($htmlOutput);
        $this->setDepthLimit($depthLimit);
        $this->setFlags($flags);
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
    
    public function dump($input, $offset = 1)
    {
    }
    
    public function export()
    {
    }
    
    public function start()
    {
    }
    
    public function end()
    {
    }
    
    public function kill()
    {
    }
    
    /**
     *
     * @param mixed $var
     * @param int $level - current depth level [interal use]
     * @param array $objInstances - map of current object instance [internal]
     */
    public function debugVar($var, $level = 0, array $objInstances = array())
    {
        $type = gettype($var);
        $ln = $this->indentLine();
        
        switch ($type) {
            case 'boolean':
                $v = $var ? 'true' : 'false';
                return $this->templateVar($type, $v);
            case 'integer':
                return $this->templateVar($type, $var);
            case 'double':
                $float = (float)$var;
                if (strlen($float) == 1 || (strlen($float) == 2 && $float < 0)) {
                    $float = number_format($var, 1);
                }
                return $this->templateVar($type, $float);
            case 'string':
                $len = strlen($var);
                if ($this->htmlOutput) {
                    $var = htmlspecialchars($var, ENT_QUOTES, 'UTF-8', false);
                }
                $var = addslashes($var);
                
                return $this->templateVar($type, $len, $var);
            case 'resource':
            case 'resource (closed)':
                return sprintf($this->templates['resource'], intval($var), get_resource_type($var));
            case 'NULL':
                return self::$NULL;
            case 'array':
                $output = '';
                $len = count($var);

                if ($len > 0) {
                    ++$level;
                    if ($level < $this->depthLimit) {
                        foreach ($var as $k => $v) {
                            //HTML escape keys
                            if (gettype($k) == 'string') {
                                if ($this->htmlOutput) {
                                    $k = htmlspecialchars($k, ENT_QUOTES, 'UTF-8', false);
                                }
                                $k = '"'.$k.'"';
                            }
                            $_v = $this->debugVar($v, $level, $objInstances); //recursive
                            $output .=  $ln . $this->indentLevel($level) . $this->templateVar('array_item', $k, $_v);
                        }
                    } else {
                        $output .= $ln . $this->indentLevel($level) . self::$DEPTH_LIMIT;
                    }
                    --$level;
                    $output .= $ln . $this->indentLevel($level);
                }
                return $this->templateVar($len, $output);
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
                    
                    if ($level < $this->depthLimit) {
                        $ReflectionObj = new \ReflectionObject($var);
                        if (self::SHOW_CONSTANTS & $flags) {
                            //CONSTANTS
                            foreach ($ReflectionObj->getConstants() as $k => $v) {
                                $output .= $ln . $this->indentLevel($level);
                                $output .= $this->templateVar(
                                    'property',
                                    $k,
                                    self::$CONSTANT,
                                    $this->debugVar($v, $level, $objInstances) //recursive
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
                            $v = $Property->getValue($var);
                            
                            //static
                            if ($Property->isStatic()) {
                                $prop_type .= ' '.self::$PRIVATE;
                            }

                            $output .= $ln . $this->indentLevel($level);
                            $output .= $this->templateVar(
                                    'property',
                                    $k,
                                    $prop_type,
                                    $this->debugVar($v, $level, $objInstances) //recurse
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
                return $this->templateVar($type, $var);
        } //end switch
    }
        

    //===================== Helpers ===============
    /**
     *
     * @param args $type
     * @param mixed ...$args
     */
    public function templateVar($type, ...$args)
    {
        return sprintf($this->templates[$type], ...$args);
    }
    
    /**
     * check if a flag is set
     *
     * @param int $flag
     */
    public function hasFlag($flag)
    {
        return $this->flags & $flag;
    }
    
    /**
     * add a new line
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
    public function backTrace($offset = 0)
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
     * get the call's backtrace
     *
     * @param number $offset
     * @return array
     */
    public function getTraceFirst($offset = 0)
    {
        $trace = self::trace($offset);
        return reset($trace);
    }
    
    /**
     * get the Output call's backtrace
     *
     * @param number $offset
     * @return string
     */
    public function getTraceFirstAsString($offset = 0)
    {
        $trace = self::getTraceFirst($offset);
        return "Output from FILE[ {$trace['file']} ] on LINE[ {$trace['line']} ]";
    }
}
