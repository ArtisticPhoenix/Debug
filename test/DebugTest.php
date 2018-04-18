<?php
use PHPUnit\Framework\TestCase;
use evo\debug\Debug;

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
class DebugTest extends TestCase
{
    /**
     *
     * @var Debug
     */
    protected $Debug;
    
    /**
     *
     */
    public function setup()
    {
        $this->Debug = Debug::getInstance('UnitTest');
        $this->Debug->setHtmlOutput(false);
        $this->Debug->setDepthLimit(4);
        $this->Debug->setFlags(Debug::SHOW_ALL);
    }
    
    /**
     *
     * @group DebugTest
     * @group testBoolean
     */
    public function testBoolean()
    {
        $this->assertEquals('bool(false)', $this->Debug->varExport(false));
        $this->assertEquals('bool(true)', $this->Debug->varExport(true));
    }
 
    /**
     *
     * @group DebugTest
     * @group testIntegers
     */
    public function testIntegers()
    {
        $this->assertEquals('int(1)', $this->Debug->varExport(1));
        $this->assertEquals('int(-1)', $this->Debug->varExport(-1));
    }
    
    /**
     *
     * @group DebugTest
     * @group testFloats
     */
    public function testFloats()
    {
        //floats with no value in decimal is a special case
        $this->assertEquals('float(1.0)', $this->Debug->varExport(1.0));
        $this->assertEquals('float(-1.0)', $this->Debug->varExport(-1.0));
        //check normal floats
        $this->assertEquals('float(1.01)', $this->Debug->varExport(1.01));
        $this->assertEquals('float(-1.01)', $this->Debug->varExport(-1.01));
    }
    
    /**
     *
     * @group DebugTest
     * @group testStrings
     */
    public function testStrings()
    {
        $this->assertEquals('string(0) ""', $this->Debug->varExport(''));
        
        $this->assertEquals('string(11) "hello world"', $this->Debug->varExport('hello world'));
        
        $this->assertEquals('string(32) "~`!@#$%^&*()_+-={}[]|\\\;\\\',./:\"M<>"', $this->Debug->varExport('~`!@#$%^&*()_+-={}[]|\\;\',./:"M<>'));
        
        $this->Debug->setHtmlOutput(true);
        
        $this->assertEquals('string(30) "&lt;strong style=\\"\\" &gt;html&lt;strong&gt;"', $this->Debug->varExport('<strong style="" >html<strong>'));
        
        
        $this->Debug->setHtmlOutput(false);
         
        $multiLine = "";
        $this->assertEquals(
            'string(35)\s"\nThe\sred\sfox\njumpped\sover\nthe\sbox.\n"',
            $this->showWhitespace($this->Debug->varExport('
The red fox
jumpped over
the box.
'))
        );
    }
    
    /**
     *
     * @group DebugTest
     * @group testResourses
     */
    public function testResourses()
    {
        $f = fopen("php://temp", "w");
        
        $this->assertEquals('resource('.intval($f).') of type ('.get_resource_type($f).')', $this->Debug->varExport($f));
        
        fclose($f);
    }
    
    /**
     *
     * @group DebugTest
     * @group testNull
     */
    public function testNull()
    {
        $this->assertEquals('null', $this->Debug->varExport(null));
    }
    
    /**
     *
     * @group DebugTest
     * @group testUnkown
     */
    public function testUnkown()
    {
        $f = fopen("php://temp", "w");
        fclose($f);
        
        $this->assertEquals('unknown(Resource id #'.intval($f).')', $this->Debug->varExport($f));
    }
    
    /**
     *
     * @group DebugTest
     * @group testArray
     */
    public function testArray()
    {
        $array = [];
        $this->assertEquals('array(0){}', $this->Debug->varExport($array));
        //test numerical array
        $array = [1];
        $this->assertEquals(
            'array(1){\n\t[0]\s=>\sint(1),\n}',
            $this->showWhitespace($this->Debug->varExport($array))
        );
        //test assoc array
        $array = ['foo' => 1];
        $this->assertEquals(
            'array(1){\n\t["foo"]\s=>\sint(1),\n}',
            $this->showWhitespace($this->Debug->varExport($array))
        );
        //test nested array
        $array = [1,2,'array0'=>[1,2,'array1'=>[3,4,'array2'=>[5,6,'array3'=>[]]]]];
        $this->assertEquals(
            'array(3){\n\t[0]\s=>\sint(1),\n\t[1]\s=>\sint(2),\n\t["array0"]\s=>\sarray(3){\n\t\t[0]\s=>\sint(1),\n\t\t[1]\s=>\sint(2),\n\t\t["array1"]\s=>\sarray(3){\n\t\t\t[0]\s=>\sint(3),\n\t\t\t[1]\s=>\sint(4),\n\t\t\t["array2"]\s=>\sarray(3){\n\t\t\t\t~DEPTH_LIMIT~\n\t\t\t},\n\t\t},\n\t},\n}',
            $this->showWhitespace($this->Debug->varExport($array))
        );
    }
    
    /**
     *
     * @group DebugTest
     * @group testObject
     */
    public function testObject()
    {
        $DebugTestItem = new DebugTestItem();
        
       // echo $this->Debug->varExport($DebugTestItem);
         
        $this->assertEquals(
            'object(DebugTestItem)#0\s(10)\s{\n\t["CONSTANT":constant]\s=>\sstring(8)\s"constant",\n\t["PUB_STATIC":public\sstatic]\s=>\sstring(10)\s"pub_static",\n\t["PRO_STATIC":protected\sstatic]\s=>\sstring(10)\s"pro_static",\n\t["PRI_STATIC":private\sstatic]\s=>\sstring(10)\s"pri_static",\n\t["pub":public]\s=>\sstring(3)\s"pub",\n\t["pro":protected]\s=>\sstring(3)\s"pro",\n\t["pri":private]\s=>\sstring(3)\s"pri",\n\t["array":public]\s=>\sarray(3){\n\t\t[0]\s=>\sint(0),\n\t\t["one"]\s=>\sint(1),\n\t\t["array"]\s=>\sarray(3){\n\t\t\t[0]\s=>\sstring(3)\s"two",\n\t\t\t[1]\s=>\sstring(5)\s"three",\n\t\t\t[2]\s=>\sstring(4)\s"four",\n\t\t},\n\t},\n\t["object":protected]\s=>\sobject(stdClass)#0\s(0)\s{},\n\t["self":private]\s=>\sobject(DebugTestItem)#0\s(0)\s{~CIRCULAR_REFRENCE~},\n}',
            $this->showWhitespace($this->Debug->varExport($DebugTestItem))
        );
    }
    
    /**
     *
     * @group DebugTest
     * @group testGetTraceFirstAsString
     */
    public function testGetTraceFirstAsString()
    {
        $this->assertEquals('Output from FILE[ '.__FILE__.' ] on LINE[ '.__LINE__.' ]', $this->Debug->getTraceFirstAsString());
    }

    /**
     *
     * @param string $string
     * @return mixed
     */
    protected function showWhitespace($string)
    {
        return str_replace(
            ["\r\n", "\n", "\t", "\s", " "],
            ['\n', '\n', '\t', '\s', '\s'],
            $string
        );
    }
}

class DebugTestItem
{
    const CONSTANT = 'constant';
    
    public static $PUB_STATIC = 'pub_static';
    protected static $PRO_STATIC = 'pro_static';
    private static $PRI_STATIC = 'pri_static';
    
    public $pub = 'pub';
    protected $pro = 'pro';
    private $pri = 'pri';
    
    public $array = [
        0,
        'one' => 1,
        'array' => ['two', 'three', 'four']
    ];
    protected $object;
    private $self;
    
    public function __construct()
    {
        $this->object = new stdClass();
        $this->self = $this;
    }
}
