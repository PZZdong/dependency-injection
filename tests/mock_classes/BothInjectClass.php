<?php
require_once 'EmptyClass.php';

/**
 * Description of BothInjectClass
 *
 * @author xiedong
 */
class BothInjectClass
{
    private $_object;
    private $_objectParam1;
    private $_objectParam2;
    
    private $_objectA;
    private $_objectAParam1;
    private $_objectAParam2;
    
    private $_objectB;
    private $_objectBParam1;
    private $_objectBParam2;
    
    public function __construct(EmptyClass $object, $objectParam1, $objectParam2=null)
    {
        $this->_object = $object;
        $this->_valueA = $objectParam1;
        $this->_valbeB = $objectParam2;
    }
    
    public function setObjectA(EmptyClass $object, $objectAParam1, $objectAParam2=null)
    {
        $this->_objectA = $object;
        $this->_objectAParam1 = $objectAParam1;
        $this->_objectAParam2 = $objectAParam2;
    }
    
    public function setObjectB(EmptyClass $object, $objectBParam1, $objectBParam2=null)
    {
        $this->_objectB = $object;
        $this->_objectBParam1 = $objectBParam1;
        $this->_objectBParam2 = $objectBParam2;
    }
    
    protected function setObjectC(EmptyClass $object, $objectCParam1, $objectCParam2=null)
    {
        exit('erorr! this message should not be show!');
    }
}
