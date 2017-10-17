<?php
namespace Jasmine\Component\DependencyInjection;
use Jasmine\Component\DependencyInjection\ClassDefinition;
use Jasmine\Component\DependencyInjection\Exception\CreateInstanceException;
use Jasmine\Component\DependencyInjection\Exception\ClassRegisterException;

/**
 * DI容器
 *
 * @author xiedong
 */
class Container
{
    const CONTAINER_ID = 'jasmine.container';
    
    private $_parameters = [];
    private $_registeredClass = [];
    private $_singletonInstances = [];

    /**
     * constructor
     * 初始化就把自己注册到容器中
     */
    public function __construct()
    {
        $classDefinition = new ClassDefinition(self::class);
        $classDefinition->setIsSingleton(true);
        $this->_registeredClass[self::CONTAINER_ID] = $classDefinition;
        $this->_singletonInstances[self::CONTAINER_ID] = $this;
    }

    /**
     * 根据ID获取一个参数值
     * 
     * @param string $id
     * @param mixed  $default
     * @return mixed
     */
    public function getParameter(string $id, $default=null)
    {
        return isset($this->_parameters[$id])
               ? $this->_parameters[$id]
               : $default;
    }

    /**
     * 设置一对id和vaule
     * 
     * @param string $id
     * @param mixed  $value
     * @return $this
     */
    public function setParameter(string $id, $value)
    {
        $this->_parameters[$id] = $value;
        return $this;
    }

    /**
     * 根据ID返回一个注册表中的类实例
     * 
     * @param string $id
     * @return Object
     * @throws CreateInstanceException
     */
    public function get($id)
    {
        if (!isset($this->_registeredClass[$id])) {
            throw new CreateInstanceException('id"'.$id.'"尚未注册，注册表中不存在！');
        }

        if (isset($this->_singletonInstances[$id])) {
            return $this->_singletonInstances[$id];
        }

        try {
            $instance = $this->_createInstance($this->_registeredClass[$id]);
            if ($this->_registeredClass[$id]->isSingleton()) {
                $this->_singletonInstances[$id] = $instance;
            }
        } catch (CreateInstanceException $e) {
            throw $e;
        }

        return $instance;
    }

    /**
     * 注册一个类到DI容器中
     * 相同ID只允许注册一次，如需再次注册次ID，请先用unregister方法将之前的移除
     * 
     * @param string $id
     * @param ClassDefinition $classDefinition
     * @return $this
     * @throws ClassRegisterException
     */
    public function register(string $id, ClassDefinition $classDefinition)
    {
        if ($id == 'jasmine.container') {
            throw new ClassRegisterException(
                '"' . self::CONTAINER_ID . '"是系统保留容器ID，不允许用户注册！'
            );
        }

        if (isset($this->_registeredClass[$id])) {
            throw new ClassRegisterException(
                'ID"'.$id.'", 已被注册，相同的ID只允许注册一次，如需重复注册，请使用unregister方法删除之前的注册！'
            );
        }

        $this->_registeredClass[$id] = $classDefinition;
        return $this;
    }

    /**
     * 根据id从注册表中删除一个已注册的class
     * 如果次id之前注册过则返回它的ClassDefinition类，如果没有则返回null
     * 
     * @param string $id
     * @return ClassDefinition | Null
     */
    public function unregister(string $id)
    {
        //系统保留id，不允许删除
        if ($id=='jasmine.container') {
            return null;
        }

        $classDefinition = isset($this->_registeredClass[$id])
                         ? $this->_registeredClass
                         : null;
        unset($this->_registeredClass[$id]);
        return $classDefinition;
    }

    /**
     * 返回注册表中已注册的数据
     * 
     * @return array
     */
    public function getRegisteredClass()
    {
        return $this->_registeredClass;
    }
    //--------------------------------------------------------------------------

    /**
     * 实例化一个类
     * 
     * @param ClassDefinition $classDefinition
     * @return Object
     * @throws CreateInstanceException
     */
    private function _createInstance(ClassDefinition $classDefinition)
    {
        $rfc = new \ReflectionClass((string)$classDefinition);
        $constructorArgs = [];
        foreach($classDefinition->getParameters() as $value) {
            $constructorArgs[] = is_string($value)
                               ? $this->_resovleParameter($value)
                               : $value;
        }
        
        try {
            $instance = $rfc->newInstanceArgs($constructorArgs);
        } catch(\Throwable $e) {
            throw new CreateInstanceException(
                '类"' .$classDefinition. '"实例化发生错误："' .$e->getMessage().'"'
            );
        } catch(\ReflectionException $e) {
            throw new CreateInstanceException(
                '类"' .$classDefinition. '"实例化发生错误："' .$e->getMessage().'"'
            );
        }
        
        foreach($classDefinition->getMethods() as $method=>$args) {
            try {
                $rfm = $rfc->getMethod($method);
            } catch(\ReflectionException $e) {
                throw new CreateInstanceException(
                    '类"' .$classDefinition. '"中不能存在方法"' .$method. '"!'
                );
            }
            if (!$rfm->isPublic()) {
                throw new CreateInstanceException(
                    '类"' .$classDefinition. '"中方法"' .$method. '"必须是public的!'
                );
            }
            $methodArgs = [];
            foreach($args as $value) {
                $methodArgs[] = is_string($value)
                              ? $this->_resovleParameter($value)
                              : $value;
            }
            
            try {
                $rfm->invokeArgs($instance, $methodArgs);
            } catch(\Throwable $e) {
                throw new CreateInstanceException(
                    '执行方法"' .$classDefinition.'::'.$method.'"发生异常："' .$e->getMessage().'"'
                );
            } catch(\ReflectionException $e) {
                throw new CreateInstanceException(
                    '执行方法"' .$classDefinition.'::'.$method.'"发生异常："' .$e->getMessage().'"'
                );
            }
        }
        return $instance;
    }

    /**
     * 解析一个参数，如果参数开头是'@'或'$'，则为引用参数
     * 
     * @param string $value
     * @return string
     */
    private function _resovleParameter(string $value)
    {
        $result = '';
        if (strpos($value, '@') === 0) {
            $result = $this->get(substr($value, 1));
        } elseif (strpos($value, '$') === 0) {
            $result = $this->getParameter(substr($value, 1));
        } else {
            $result = $value;
        }
        return $result;
    }
}
