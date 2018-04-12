# Debug for PHP V5.6+

This is a simple debug print class, it's main features are  

 - Ajustable visibillity, print public/protected/private properties, constants etc.
 - Ajustable depth limits, limit how deep the debugger looks in nested data
 - Circular refrence safe
 - Auto back tracing, prints the file an line where the debuging function was called
 - Debug/exit
 - Debug/output buffering
 - overall better formating
 
### Class refrence ###
```php   
    //construct or get an instance of Debug
    public static function getInstance($alias='');
    //check if a given alias is instantiated
    public static function isInstantiated($alias='')
    //register the procedural function
    public static function regesterFunctions();
    //check if HTML mode is on
    public function getHtmlOutput();
    //change output to HTML mode
    public function setHtmlOutput($htmlOutput);
    //get the depth limit
    public function getDepthLimit();
    //set the depth limit (how deep to dig into nested arrays and objects)
    public function setDepthLimit($depthLimit);
    //get the flags that are set
    public function getFlags();
    //set flags
    public function setFlags($flags);
    //check if a flag is set
    public function hasFlag($flag);
    //Debug and output 
    public function dump($input, $offset = 0);
    //Debug and return 
    public function export($input, $offset = 0);
    //Start debugging output buffer, output will be capture until flush or end is called
    public function start($offset = 0);
    //End debugging output buffer, and return it
    public function flush($offset = 0);
    //End debugging output buffer, and output it
    public function end($offset = 0);
    //Kill PHP execution with a message and a 
    public function kill($input, $offset = 0);
    //debug without the outer formatting, output it
    public function varDump($input);
    //debug without the outer formatting, return it
    public function varExport($input, $level = 0, array $objInstances = array());
    //return the part of the backtrace where this function was called from
    public function trace($offset = 0);
    //print a backtrace - formatted like a stacktrace
    public function backTrace($offset = 0);
    //return a backTrace
    public function getTraceFirst($offset = 0);
    //return the formatted trace of getTraceFirst.
    public function getTraceFirstAsString($offset = 0);
```
### Properties ###

 Name              |   Type   |   Required  | Description
 ----------------- | -------- | ----------- | ------------------------------------------------------
 $alias            |  string  |      no     | name of a given instance of Debug
 $htmlOutput       |  boolean |      no     | Switch between HTML and Text output
 $depthLimit       |  integer |      no     | Max nesting level to output
 $flags            |  bitwise |      no     | Options - see Flags
 $input            |  mixed   |      yes    | Input to process (variables to debug)
 $offset           |  integer |      no     | Offeset for backtracing (used to print where debug was called from)
 $level            |  integer |      no     | current depth level (internal use)
 $objInstances     |  array   |      no     | tracking array for object instance (interal use)
 
 
### Object Flags ###

 Name               | Description
 ------------------ | -----------------------------------------------------------------------------
 SHOW_CONSTANTS     | Include object constants in output
 SHOW_PUBLIC        | Include public properties in output
 SHOW_PROTECTED     | Include protected properties in output
 SHOW_PRIVATE       | Include private properties in output
 SHOW_ACCESSIBLE    | Include constants and public properties in output
 SHOW_VISABLE       | Include constants and public properties and protected properties in output
 SHOW_ALL           | Include all of the above in output
 
Flags are bitwise and can be set like this `SHOW_CONSTANTS | SHOW_PUBLIC` the same way PHP canstants for variouse things are handled.  The default is `SHOW_ALL`

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
 
These are output in a format much like PHP's built in `var_dump` as I find that the most usefull format.
 
It is circular refrence safe, unlike many of PHP's built in output function.  A simple example of a circular refrence is an object that stores a refrence to itself in one of it's properties.  Another example is an object that stores a refrence to a second object that stores a refrence to the first object.  In PHP's built in functions, this results in infinate recursion.  The Debugger instead replaces the circular refrence with a simple place holder `~CIRCULAR_REFRENCE~`.

Simularaly it also has protection or limits on the depth it will look at when outputing.  This limit can be set in the constructor.  Once the depth limit is reached a place holder will be substitued `~DEPTH_LIMIT~`.
 
### Example ###
```php
================================= evo\debug\Debug::dump ==================================
Output from FILE[ {yourpath}\index.php ] on LINE[ 25 ]
------------------------------------------------------------------------------------------
object(DebugTestItem)#0 (10) {
        ["CONSTANT":constant] => string(8) "constant",
        ["PUB_STATIC":public private] => string(10) "pub_static",
        ["PRO_STATIC":protected private] => string(10) "pro_static",
        ["PRI_STATIC":private private] => string(10) "pri_static",
        ["pub":public] => string(3) "pub",
        ["pro":protected] => string(3) "pro",
        ["pri":private] => string(3) "pri",
        ["array":public] => array(3){
                [0] => int(0),
                ["one"] => int(1),
                ["array"] => array(3){
                        [0] => string(3) "two",
                        [1] => string(5) "three",
                        [2] => string(4) "four",
                },
        },
        ["object":protected] => object(stdClass)#0 (0) {},
        ["self":private] => object(DebugTestItem)#0 (0) {~CIRCULAR_REFRENCE~},
}
==========================================================================================
```
Please note that `{yourpath}` will be the actual path to the index file on your system.  This is exteemly useful if you are like me and forget where you put all your print function.

Debug is a Multiton, or a collection wrapper for singletons.  This means you cannot construct this class manually.  To construct it call `$D = Debug::getInstance('alias')`.

For ease of access you can use the procedural functions after calling `Debug::regesterFunctions()`. The procedural function area all named `debug_{methodname}`.  So for example you can call `$Debug->dump()` with the function `debug_dump()`.


### Release Notes ###

  - 1.0.0 - init release
  - 1.0.1 - added procedural functions
  - 1.0.3 - shortened function names
  
  
  
  