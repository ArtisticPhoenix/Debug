# Debug for PHP V5.6+

This is a simple debug print class, it's main features are  

 - Ajustable visibillity, print public/protected/private properties, constants etc.
 - Ajustable depth limits, limit how deep the debugger looks in nested data
 - Circular refrence safe
 - Auto back tracing, prints the file an line where the debuging function was called
 - Debug/exit
 - Debug/output buffering
 - overall better formating
 
 Class refrence
```php
    /**
     * @param string $html
     * @param int $depthLimit
     * @param int $flags - one or more of the SHOW_* constants
     */
	public function __construct($htmlOutput = true, $depthLimit = 10, $flags = self::SHOW_ALL);
	
	/**
     * Switch betwen HTML and TEXT output formats
     * @return boolean
     */
	public function getHtmlOutput();
	public function setHtmlOutput($htmlOutput);
	public function getDepthLimit();
	public function setDepthLimit($depthLimit);
	public function getFlags();
	public function setFlags($flags);
	public function dump($input, $offset = 0);
	public function export($input, $offset = 0);
	public function start($offset = 0);
	public function end($offset = 0);
	public function kill($input, $offset = 0);
	public function debugVar($var, $level = 0, array $objInstances = array());
	public function templateVar($type, ...$args);
	public function hasFlag($flag);
	public function trace($offset = 0)'
	public function backTrace($offset = 0)'
	public function getTraceFirst($offset = 0);
	public function getTraceFirstAsString($offset = 0);	
```
 
 


The debuger can handle any type provided by PHP's `gettype()`.

 - boolean
 - integer
 - double
 - string
 - resource
 - NULL
 - array
 - object
 - unkown type
 
It is circular refrence safe, unlike many of PHP's built in output function.  A simple example of a circular refrence is an object that stores a refrence to itself in one of it's properties.  Another example is an object that stores a refrence to a second object that stores a refrence to the first object.  In PHP's built in functions, this results in infinate recursion.  The Debugger instead replaces the circular refrence with a simple place holder `~CIRCULAR_REFRENCE~`.

Simularaly it also has protection or limits on the depth it will look at when outputing.  This limit can be set in the constructor.  Once the depth limit is reached a place holder will be substitued `~DEPTH_LIMIT~`.
 

