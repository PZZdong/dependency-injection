<?php
require_once 'mock_classes/EmptyClass.php';
require_once 'mock_classes/ContructorInjectClass.php';
require_once 'mock_classes/MethodInjectClass.php';
require_once 'mock_classes/CanotInjectClass.php';
require_once 'mock_classes/BothInjectClass.php';

use PHPUnit\Framework\TestCase;
use Jasmine\Component\DependencyInjection\ClassDefinition;
use Jasmine\Component\DependencyInjection\Container;
/**
 * Description of ContainerTest
 *
 * @author xiedong
 */
class ContainerTest extends TestCase
{
    /**
     * @var Jasmine\Component\DependencyInjection\Container 
     */
    protected static $_container;

    public static function setUpBeforeClass()
    {
        self::$_container = new Container();
    }
    //--------------------------------------------------------------------------

    /**
     * 测试注册及获取正确对象
     */
    public function testGetContainer()
    {
        //注册参数
        self::$_container->setParameter('parameters.param1', 'p1')
                         ->setParameter('parameters.param2', 'p2');

        //获取自身
        $containter = self::$_container->get(Container::CONTAINER_ID);
        $this->assertTrue($containter instanceof Container);

        //注册及获取EmptyClass
        $emptyClassDef = new ClassDefinition(EmptyClass::class);
        self::$_container->register('test.empty_class', $emptyClassDef);
        $emptyClass = self::$_container->get('test.empty_class');
        $this->assertTrue($emptyClass instanceof EmptyClass);

        //注册及获取ContructorInjectClass
        $contructorInjectClassDef = new ClassDefinition(ContructorInjectClass::class);
        $contructorInjectClassDef->setParameters([
            '@test.empty_class', '$parameters.param1'
        ]);
        self::$_container->register('test.contructor_inject_class', $contructorInjectClassDef);
        $contructorInjectClass = self::$_container->get('test.contructor_inject_class');
        $this->assertTrue($contructorInjectClass instanceof ContructorInjectClass);
        $this->assertTrue($contructorInjectClass->object instanceof EmptyClass);
        $this->assertEquals($contructorInjectClass->objectParam1, self::$_container->getParameter('parameters.param1'));
        $this->assertEquals($contructorInjectClass->objectParam2, null);

        //注册及获取MethodInjectClass
        $methodInjectClassDef = new ClassDefinition(MethodInjectClass::class);
        $methodInjectClassDef->addMethod('setObjectA', [
            '@test.empty_class', '$parameters.param1'
        ]);
        $methodInjectClassDef->addMethod('setObjectB', [
            '@test.empty_class', '$parameters.param1', '$parameters.param2'
        ]);
        self::$_container->register('test.method_inject_class', $methodInjectClassDef);
        $methodInjectClass = self::$_container->get('test.method_inject_class');
        $this->assertTrue($methodInjectClass instanceof MethodInjectClass);
        $this->assertTrue($methodInjectClass->objectA instanceof EmptyClass);
        $this->assertEquals($methodInjectClass->objectAParam1, self::$_container->getParameter('parameters.param1'));
        $this->assertEquals($methodInjectClass->objectAParam2, null);
        $this->assertTrue($methodInjectClass->objectB instanceof EmptyClass);
        $this->assertEquals($methodInjectClass->objectBParam1, self::$_container->getParameter('parameters.param1'));
        $this->assertEquals($methodInjectClass->objectBParam2, self::$_container->getParameter('parameters.param2'));

        //注册BothInjectClass
        $bothInjectClassDef = new ClassDefinition(BothInjectClass::class);
        $bothInjectClassDef->setParameters([
            '@test.empty_class', '$parameters.param1', '$parameters.param2'
        ]);
        $bothInjectClassDef->addMethod('setObjectA', [
            '@test.empty_class', '$parameters.param1'
        ]);
        self::$_container->register('test.both_inject_class', $bothInjectClassDef);
        $bothInjectClass = self::$_container->get('test.both_inject_class');
        $this->assertTrue($bothInjectClass instanceof BothInjectClass);
    }

    /**
     * 测试注册及获取正确对象(重复注册)
     * @expectedException Jasmine\Component\DependencyInjection\Exception\ClassRegisterException
     */
    public function testClassRegisterException()
    {
        $emptyClassDef = new ClassDefinition(EmptyClass::class);
        self::$_container->register('test.empty_class', $emptyClassDef);
    }

    /**
     * 测试让DI容器实例化一个缺少构造参数的类
     * @expectedException Jasmine\Component\DependencyInjection\Exception\CreateInstanceException
     */
    public function testCreateInstanceInvalidParameterException()
    {
        //注册及获取ContructorInjectClass
        $contructorInjectClassDef = new ClassDefinition(ContructorInjectClass::class);
        $contructorInjectClassDef->setParameters([
            '@test.empty_class'
        ]);
        self::$_container->register('test.contructor_inject_class_error', $contructorInjectClassDef);
        self::$_container->get('test.contructor_inject_class_error');
    }

    /**
     * 测试让DI容器实例化一个不可调用方法的类
     * @expectedException Jasmine\Component\DependencyInjection\Exception\CreateInstanceException
     */
    public function testCreateInstanceByPrivateMethodException()
    {
        //注册及获取MethodInjectClass
        $methodInjectClassDef = new ClassDefinition(MethodInjectClass::class);
        $methodInjectClassDef->addMethod('setObjectA', [
            '@test.empty_class', '$parameters.param1'
        ]);
        $methodInjectClassDef->addMethod('setObjectB', [
            '@test.empty_class', '$parameters.param1', '$parameters.param2'
        ]);
        $methodInjectClassDef->addMethod('setObjectC', [
            '@test.empty_class', '$parameters.param1', '$parameters.param2'
        ]);
        self::$_container->register('test.method_inject_class_test', $methodInjectClassDef);
        self::$_container->get('test.method_inject_class_test');
    }
    
    //todo...  防止DI容器中产生循环调用的结构
}
