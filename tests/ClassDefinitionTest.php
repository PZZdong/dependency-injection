<?php
require_once 'mock_classes/EmptyClass.php';
require_once 'mock_classes/ContructorInjectClass.php';
require_once 'mock_classes/MethodInjectClass.php';
require_once 'mock_classes/CanotInjectClass.php';
require_once 'mock_classes/BothInjectClass.php';

use PHPUnit\Framework\TestCase;
use Jasmine\Component\DependencyInjection\ClassDefinition;

/**
 * Description of ClassDefinitionTest
 *
 * @author xiedong
 */
class ClassDefinitionTest extends TestCase
{
    /**
     * 测试构造一个不存在的类的ClassDefinition
     * 
     * @expectedException Jasmine\Component\DependencyInjection\Exception\ClassDefinitionException
     */
    public function testUndefinedClassException()
    {
        new ClassDefinition('A\B\C');
    }
    
    /**
     * 测试构造一个不可实例化的类（abstract\interface\trait）
     * 
     * @expectedException Jasmine\Component\DependencyInjection\Exception\ClassDefinitionException
     */
    public function testUninstanceClassException()
    {
        new ClassDefinition('CanotInjectClass');
    }
}
