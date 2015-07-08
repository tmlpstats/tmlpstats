<?php
namespace TmlpStats\Tests;

class TestAbstract extends \PHPUnit_Framework_TestCase
{
    protected $testClass = '';

    protected function getObjectMock($methods = array(), $constructorArgs = array())
    {
        if (!$this->testClass) {
            $class = get_class($this);
            throw new \Exception("Test class $class did not specify the class under test, testClass.");
        }
        return $this->getMockBuilder($this->testClass)
                    ->setMethods($methods)
                    ->setConstructorArgs($constructorArgs)
                    ->getMock();
    }

    protected function mergeMockMethods($defaultMethods, $methods)
    {
        return array_unique(array_merge($defaultMethods, $methods));
    }

    protected function setProperty(&$object, $name, $value)
    {
        $reflector = new \ReflectionClass(get_class($object));

        $property = $reflector->getProperty($name);
        $property->setAccessible(true);

        return $property->setValue($object, $value);
    }

    protected function getProperty($object, $name)
    {
        $reflector = new \ReflectionClass(get_class($object));

        $property = $reflector->getProperty($name);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    protected function runMethod($object, $name)
    {
        $reflector = new \ReflectionClass(get_class($object));

        $method = $reflector->getMethod($name);
        $method->setAccessible(true);

        $arguments = func_get_args();

        return $method->invokeArgs($object, array_slice($arguments, 2));
    }
}
