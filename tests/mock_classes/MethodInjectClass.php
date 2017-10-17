<?php
require_once 'EmptyClass.php';

/**
 * Description of MethodInjectClass
 *
 * @author xiedong
 */
class MethodInjectClass
{
    public $objectA;
    public $objectAParam1;
    public $objectAParam2;

    public $objectB;
    public $objectBParam1;
    public $objectBParam2;

    public function setObjectA(EmptyClass $object, $objectAParam1, $objectAParam2=null)
    {
        $this->objectA = $object;
        $this->objectAParam1 = $objectAParam1;
        $this->objectAParam2 = $objectAParam2;
    }

    public function setObjectB(EmptyClass $object, $objectBParam1, $objectBParam2=null)
    {
        $this->objectB = $object;
        $this->objectBParam1 = $objectBParam1;
        $this->objectBParam2 = $objectBParam2;
    }

    protected function setObjectC(EmptyClass $object, $objectCParam1, $objectCParam2=null)
    {
        exit('erorr! this message should not be shown!');
    }
}
