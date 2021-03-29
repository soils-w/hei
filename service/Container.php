<?php

namespace service;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use think\Exception;
use ArrayAccess;
use IteratorAggregate;
use Countable;
use ArrayIterator;

/**
 * 容器管理类 支持PSR-11
 */
class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable
{
    /**
     * @var array
     * 绑定容器标识
     */
    protected $bind = [];

    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances = [];

    /**
     * @var Container|Closure
     * 当前容器对象实例
     */
    protected static $instance;

    /**
     * @param $instance
     * 设置当前容器的示例
     */
    public static function setInstance($instance)
    {
        static::$instance = $instance;
    }

    /**
     * @return Closure|Container
     * 获取当前容器的实例，没有就创建实例
     */
    public static function getInstance()
    {
        if(is_null(static::$instance)) {
            static::$instance = new static();
        }

        if(static::$instance instanceof \Closure){
            return (static::$instance)();
        }
        return static::$instance;
    }

    /**
     * 查看当前容器是否有某类的实例化
     * @param $instance 类名，ex:app\model\Basic
     * @return bool
     */
    public function exists($abstract)
    {
        return isset($this->instances[$abstract]);
    }

    /**
     * 绑定一个实例到容器中
     * @param string $abstract
     * @param $instance
     * @return $this
     */
    public function instance(string $abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
        return $this;
    }

    /**
     * @param string $abstract
     * @return mixed|string
     */
    public function get(string $abstract)
    {
        //判断有没有存在类的实例
        if($this->exists($abstract)) {
            $this->make($abstract);
        } else {//Todo 抛异常
            return 'class not exists: '.$abstract;
        }
    }

    /**
     * 绑定一个类的实例，闭包，接口实现，类到容器中
     * @param string $abstract
     * @param null $concrete
     * @return $this
     */
    public function bind(string $abstract, $concrete = null) {
        if(is_array($abstract)) {
            foreach($abstract as $k => $v) {
                $this->bind($k, $v);
            }
        } elseif ($abstract instanceof \Closure) {//闭包
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($abstract)) {//类的实例
            $this->instance($abstract, $concrete);
        } else {
            $this->bind[$abstract] = $concrete;
        }
        return $this;
    }

    public function make(string $abstract, array $vals = [], bool $newInstance = false)
    {
        //存在则直接返回
        if(isset($this->instances[$abstract]) && !$newInstance){
            return $this->instances[$abstract];
        }
        //没有的话，是闭包函数就通过反射执行方法，是类就通过反射实例化类
        if(isset($this->bind[$abstract]) && $this->bind[$abstract] instanceof \Closure) {
            //通过反射执行方法
            $object = $this->invokeFunction($this->bind[$abstract], $vals);
        } else {
            //通过反射实例化类
            $object = $this-> invokeClass($abstract, $vals);
        }

        if(!$newInstance) {
            $this->instances[$abstract] = $object;
        }
        return $object;
    }

    /**
     * 通过反射实例化类
     * @param string $class
     * @param array $vals
     * @return mixed|object
     * @throws ReflectionException
     */
    public function invokeClass(string $class, array $vals = [])
    {
        try {
            //通过反射实例化类
            $reflect = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new \Exception('class not exists: ' . $class);
        }

        if($reflect -> hasMethod('__make')) {
            $method = $reflect -> getMethod('__make');
            if($method->isPublic() && $method->isStatic()) {
                $args = $this->bindParams($method, $vals);
                return $method->invokeArgs(null, $args);
            }
        }

        // 获取类的构造函数
        $constructor = $reflect->getConstructor();
        $args = $constructor ? $this->bindParams($constructor, $vals):[];
        $object = $reflect -> newInstanceArgs($args);
        return $object;
    }

    /**
     * @param ReflectionFunctionAbstract $reflect 反射类
     * @param array $vals 参数
     * @return array
     */
    public function bindParams(ReflectionFunctionAbstract $reflect, array $vars =[])
    {
        //没有参数的话直接返回空
        if($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        // 判断数组类型 数字数组时按顺序绑定参数
        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;
        $params = $reflect->getParameters();
        $args   = [];
        foreach($params as $param) {
            $name      = $param->getName();
            $lowerName = $this->humpToLine($name);
            $class     = $param->getClass();
            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif (1 == $type && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif (0 == $type && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif (0 == $type && isset($vars[$lowerName])) {
                $args[] = $vars[$lowerName];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new Exception('method param miss:' . $name);
            }
        }
        return $args;
    }

    /**
     * 获取对象类型的参数值
     * @access protected
     * @param string $className 类名
     * @param array  $vars      参数
     * @return mixed
     */
    protected function getObjectParam(string $className, array &$vars)
    {
        $result = $this->make($className);

        return $result;
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     * @access public
     * @param string|Closure $function 函数或者闭包
     * @param array          $vars     参数
     * @return mixed
     */
    public function invokeFunction($function,array $vals)
    {
        try {
            $reflect = new ReflectionFunction($function);
        } catch (ReflectionException $e) {
            throw new \Exception("function not exists: {$function}()", $function, $e);
        }
        $args = $this->bindParams($reflect,$vals);

        return $function(...$args);
    }

    /**
     * 删除容器中的对象实例
     * @access public
     * @param string $name 类名或者标识
     * @return void
     */
    public function delete($name)
    {
        if (isset($this->instances[$name])) {
            unset($this->instances[$name]);
        }
    }

    /*
     * 驼峰转下划线
     */
    public static function humpToLine($str)
    {
        $str = str_replace("_", "", $str);
        $str = preg_replace_callback('/([A-Z]{1})/', function ($matches) {
            return '_' . strtolower($matches[0]);
        }, $str);
        return strtolower(ltrim($str, "_"));
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return $this->exists($name);
    }

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->bind($name, $value);
    }

    /**
     * @param $name
     * @return mixed|string
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name): bool
    {
        return $this->exists($name);
    }

    public function __unset($name)
    {
        $this->delete($name);
    }

    public function offsetExists($key)
    {
        return $this->exists($key);
    }

    public function offsetGet($key)
    {
        return $this->make($key);
    }

    public function offsetSet($key, $value)
    {
        $this->bind($key, $value);
    }

    public function offsetUnset($key)
    {
        $this->delete($key);
    }

    //Countable
    public function count()
    {
        return count($this->instances);
    }

    //IteratorAggregate
    public function getIterator()
    {
        return new ArrayIterator($this->instances);
    }

}