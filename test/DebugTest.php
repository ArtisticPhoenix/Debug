<?php
use PHPUnit\Framework\TestCase;
use evo\debug\Debug;

class DebugTest extends TestCase
{
    protected $Debug;
    

    public function setup()
    {
        $this->Debug = new Debug(false, 4, Debug::SHOW_ALL);
    }
    
    /**
     * @group DebugTest
     * @group testBoolean
     */
    public function testBoolean()
    {
        $this->assertEquals('bool(false)', $this->Debug->debugVar(false));
        $this->assertEquals('bool(true)', $this->Debug->debugVar(true));
    }
 
    /**
     * @group DebugTest
     * @group testIntegers
     */
    public function testIntegers()
    {
        $this->assertEquals('int(1)', $this->Debug->debugVar(1));
        $this->assertEquals('int(-1)', $this->Debug->debugVar(-1));
    }
    
    /**
     * @group DebugTest
     * @group testFloats
     */
    public function testFloats()
    {
        //floats with no value in decimal is a special case
        $this->assertEquals('float(1.0)', $this->Debug->debugVar(1.0));
        $this->assertEquals('float(-1.0)', $this->Debug->debugVar(-1.0));
        //check normal floats
        $this->assertEquals('float(1.01)', $this->Debug->debugVar(1.01));
        $this->assertEquals('float(-1.01)', $this->Debug->debugVar(-1.01));
    }
    
    /**
     * @group DebugTest
     * @group testStrings
     */
    public function testStrings()
    {
        $this->assertEquals('string(5) "hello"', $this->Debug->debugVar("hello"));
        
        $this->assertEquals('string(5) ""', $this->Debug->debugVar(''));
        
        $this->assertEquals('string(5) "with single quote"', $this->Debug->debugVar("~`!@#$%^&*()_+-={}[]|\\;',./:\"M<>"));
        
         
        $multiLine = "
The red fox
jumpped over
the box.
";
        $this->assertEquals(
            'string('.strlen($multiLine).') "'.$multiLine.'"',
            $this->Debug->debugVar($multiLine)
        );
    }
    
    /**
     * @group DebugTest
     * @group testResourses
     */
    public function testResourses()
    {
        $f = fopen("php://temp", "w");
        
        $this->assertEquals('resource('.intval($f).') of type ('.get_resource_type($f).')', $this->Debug->debugVar($f));
        
        fclose($f);
    }
    
    /**
     * @group DebugTest
     * @group testNull
     */
    public function testNull()
    {
        $this->assertEquals('null', $this->Debug->debugVar(null));   
    }
    
    /**
     * @group DebugTest
     * @group testUnkown
     */
    public function testUnkown()
    {
        $f = fopen("php://temp", "w");
        fclose($f);
        
        $this->assertEquals('unknown(Resource id #'.intval($f).')', $this->Debug->debugVar($f));   
    }
    
    /**
     * @group DebugTest
     * @group testArray
     */
    public function testArray()
    {
        $array = [];
        $this->assertEquals('array(0){}', $this->Debug->debugVar($array));
        //test numerical array
        $array = [1];
        $this->assertEquals('array(1){[0]=>int(1),}', $this->Debug->debugVar($array));
        //test assoc array
        $array = ['foo' => 1];
        $this->assertEquals('array(1){["foo"]=>int(1),}', $this->Debug->debugVar($array));
        //test nested array
        $array = [1,2,'array0'=>[1,2,'array1'=>[3,4,'array2'=>[5,6,'array3'=>[]]]]];
        $this->assertEquals(
            'array(3){[0]=>int(1),[1]=>int(2),["array0"]=>array(3){[0]=>int(1),[1]=>int(2),["array1"]=>array(3){[0]=>int(3),[1]=>int(4),["array2"]=>array(3){::DEPTH_LIMIT::},},},}',
            $this->Debug->debugVar($array)
        );
        
        
       /* $array[] = false;
        $array[] = 1;
        $array[] = 1.0;
        $array[] = 'string';
        $f = fopen("php://temp", "w");
        $array[] = $f;
        $g = fopen("php://temp", "w");
        fclose($g);
        $array[] = $g;
        $array[] = null;
        
        $this->assertEquals('array(0){}', $this->Debug->debugVar($array));
        
        fclose($f);*/
    }
    
 
    
}

class fakeObject{
    
    const CONSTANT = 'constant';
    
    
}
