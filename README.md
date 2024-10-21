# Debug for PHP

This is a fully featured debug output/print class, it's main features are  

 - Ajustable visibillity, print public/protected/private properties, constants etc.
 - Ajustable depth limits, limit how deep the debugger looks in nested data
 - Circular refrence safe (eg. an object refrences itself)
 - Auto back tracing, prints the file and line where the debuging function was called from
 - Debug/exit
 - Debug/output buffering
 - Stack tracing
 - can be called from included functions
 - overall better formating, simular to `var_dump` 
 
### Class refrence ###
```php   
    //construct or get an instance of Debug
    public static function getInstance(string $alias=''): self;
    //check if a given alias is instantiated
    public static function isInstantiated(string $alias=''): bool
    //register the procedural function
    public static function regesterFunctions(): void;
    //check if HTML mode is on
    public function getHtmlOutput(): bool;
    //change output to HTML mode [default is false]
    public function setHtmlOutput(bool $htmlOutput): void;
    //get the depth limit
    public function getDepthLimit(): int;
    //set the depth limit (how deep to dig into nested arrays and objects)
    public function setDepthLimit(int $depthLimit);
    //get the flags that are set
    public function getFlags(): int;
    //set flags
    public function setFlags(int $flags): void;
    //check if a flag is set
    public function hasFlag(int $flag): bool;
    //Debug and output 
    public function dump(mixed $input=null, int $offset = 0);
    //Debug and output 
    public function dumpException(Throwable $exception, int $offset = 0);
    //Debug and return 
    public function export(mixed $input=null, $offset = 0): string;
    //Start debugging output buffer, output will be capture until flush or end is called
    public function start(int $offset=0): void;
    //End debugging output buffer, and return it
    public function flush(int $offset=0): void;
    //End debugging output buffer, and output it
    public function end(int $offset=0): string;
    //Kill PHP execution with a message and a 
    public function kill(mixed $input=null, $offset=0);
    //debug without the outer formatting, output it
    public function varDump(mixed $input);
    //debug without the outer formatting, return it
    public function varExport(mixed $input, int $level=0, array $objInstances=array()): string;
    //return the part of the backtrace where this function was called from
    public function trace(int $offset=0): array;
    //print a backtrace - formatted like a stacktrace
    public function backTrace(int $offset=0): void;
    //return a backTrace
    public function getTraceFirst(int $offset=0): arrat;
    //return the formatted trace of getTraceFirst.
    public function getTraceFirstAsString(int $offset=0): string;
```
### Properties ###

 Name              |   Type   |   Required  | Description
 ----------------- | -------- | ----------- | ------------------------------------------------------
 $alias            |  string  |      no     | name of a given instance of Debug
 $htmlOutput       |  boolean |      no     | Switch between HTML and Text output
 $depthLimit       |  integer |      no     | Max nesting level to output
 $flags            |  bitwise |      no     | Options - see Flags
 $input            |  mixed   |      yes    | Input to process (variables to debug)
 $offset           |  integer |      no     | Manual Offeset for backtracing (backtrace where debug was called from). Backtracking should be done automatically, but it may fail in some "edge" cases. This allows you to manually set the offset (see function calls for example)
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
 
These are output in a format much like PHP's built in `var_dump` as I find that the most usefull format.  When in HTML output mode, debug is still returned as a string.  It has a set of `<pre>` tags added in to preserve whitespace and string variables are ran though `htmlspecialchars($input, ENT_NOQUOTES, 'UTF-8', false)` as to not inject the debug data as HTML into the page.
 
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

For ease of access you can use the procedural functions after calling `Debug::regesterFunctions()`. The procedural function area all named `debug_{methodname}`.  So for example you can call `$Debug->dump()` with the function `debug_dump()`.  You can access the function instance by using the `Debug::ALIAS_FUNCTIONS` constant, such as `$instance = Debug::getInstance(Debug::ALIAS_FUNCTIONS)`.  One would do this, for example, to change the output from text to HTML or to change the visibillity flags.  Then the functions will use this instance and any custom settings you make to it.

An example of Manual offset is in the debug functions

**index.php**
```php
//require composer PSR4 autoloader
require_once 'vendor/autoload.php';
//regester the procedural functions for debug
Debug::regesterFunctions();
//example of modifing the depth limit for the instance used in the procedural functions
Debug::getInstance(Debug::ALIAS_FUNCTIONS)->setDepthLimit(4);

debug_dump("foo"); //we'll say this is line 8 of index.php
```

**src/functions.php**
```php
if (!function_exists('debug_dump')) {
    /**
     *
     * {@inheritDoc}
     * @see \evo\debug\Debug::dump()
     */
    function debug_dump($input, $offset=1)
    {
        Debug::getInstance(Debug::ALIAS_FUNCTIONS)->dump($input, $offset); //this is line 20 of functions.php
    }
}
```

**src/Debug.php**
```php
public function dump($input, $offset = 0)
{
  ...
}
```

As you can see the `$offset=1` for `debug_dump()` has a default of **1**, this is set to **0** in the class itself. The reason for this (and for having a manual offset) is because we are wrapping the method call in a function. If we didn't modify the offset Debug would return the location it was called which is in the __src/functions.php__ file on like 20.  This is not what we want, we actualy want where the `debug_dump` functoin was called from, in this example line 8 from __index.php__.  There is no way to know this is the intention from inside the **Debug** class when it builds the back trace.  Because it's 1 call away from the actual class call, we set it as 1. Then Debug knows to shift the backtrace by that offset so that it displays the correct file and line number that we actually want.


### Instalation 

The prefer way to instal is to include it in you composer.json file as this project depends on another one of my projects named [Pattern](https://github.com/ArtisticPhoenix/Pattern).  So if you just download it directly it wont have that dependancy unless you run the composer file included in the project.

```
{
   "require" : {
		"evo/debug" : "~1.0"
	}
}
```


### Release Notes ###

  - 1.0.0 - init release
  - 1.0.1 - added procedural functions
  - 1.0.3 - shortened function names
  
  
  
  
