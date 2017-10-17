<?php
namespace Jasmine\Component\DependencyInjection;
use Jasmine\Component\DependencyInjection\Exception\ClassDefinitionException;

/**
 * 本对象用来封装一个可被实例化的类的信息
 *
 * @author xiedong
 */
class ClassDefinition implements \Serializable
{
    /**
     * class name (include namespace)
     * @var string
     */
    private $_class = '';

    private $_description = '';

    /**
     * is singleton
     * @var boolean
     */
    private $_isSingleton = false;
    
    /**
     * The constructor parameters
     * @var array
     */
    private $_parameters = [];
    
    /**
     * Methods and parameters in here.
     * @var array 
     */
    private $_methods = [];
    //--------------------------------------------------------------------------

    /**
     * __constructor
     * 
     * @param string $class
     * @throws ClassDefinitionException
     */
    public function __construct(string $class, string $description='这家伙很懒，什么都没说......')
    {
        if (!class_exists($class)) {
            throw new ClassDefinitionException('类"'.$class.'"找不到！');
        }
        if ( !(new \ReflectionClass($class))->isInstantiable() ) {
            throw new ClassDefinitionException('类"'.$class.'"不能被实例化！');
        }
        $this->_class = $class;
        $this->_description = $description;
    }

    /**
     * set isSingleton
     * 
     * @param bool $flag
     */
    public function setIsSingleton(bool $flag)
    {
        $this->_isSingleton = (bool)$flag;
        return $this;
    }

    /**
     * get isSingleton
     * 
     * @return bool
     */
    public function isSingleton()
    {
        return $this->_isSingleton;
    }

    /**
     * set constructor parameters
     * 
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;
        return $this;
    }

    /**
     * get constructor parameters
     * 
     * @return array
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    /**
     * add method and parameters
     * 
     * @param type $method
     * @param array $parameters
     */
    public function addMethod($method, array $parameters)
    {
        $this->_methods[$method] = $parameters;
        return $this;
    }

    /**
     * Get all methods
     * 
     * @return type
     */
    public function getMethods()
    {
        return $this->_methods;
    }
    
    /**
     * get description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }
    
    //--------------------------------------------------------------------------
    
    /**
     * implement Serializable interface
     * 
     * @return string
     */
    public function serialize()
    {
        return serialize(
            $this->_class,
            $this->_isSingleton,
            $this->_parameters,
            $this->_methods
        );
    }

    /**
     * implement Serializable interface
     * 
     * @param type $serialized
     */
    public function unserialize($serialized)
    {
        list(
            $this->_class,
            $this->_isSingleton,
            $this->_parameters,
            $this->_methods
        ) = unserialize($serialized);
    }

    /**
     * return the proxy class name
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->_class;
    }
}
