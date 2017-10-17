<?php
require_once 'EmptyClass.php';

/**
 * Description of ContructorClass
 *
 * @author xiedong
 */
class ContructorInjectClass
{
    public $object;
    public $objectParam1;
    public $objectParam2;

    public function __construct(EmptyClass $object, $objectParam1, $objectParam2=null)
    {
        $this->object = $object;
        $this->objectParam1 = $objectParam1;
        $this->objectParam2 = $objectParam2;
    }
}
