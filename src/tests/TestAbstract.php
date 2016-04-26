<?php
namespace TmlpStats\Tests;

use Artisan;
use Carbon\Carbon;

set_time_limit(600);

class TestAbstract extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';
    protected $testClass = '';
    protected $instantiateApp = false;
    protected $runMigrations = false;
    protected $runSeeds = false;

    protected $now;

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = null;
        if ($this->instantiateApp) {
            $app = require __DIR__ . '/../bootstrap/app.php';
            $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        }

        if ($this->runMigrations) {
            Artisan::call('migrate');
        }

        if ($this->runSeeds) {
            Artisan::call('db:seed');
        }

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $this->now = Carbon::now();
        Carbon::setTestNow($this->now);
    }

    public function tearDown()
    {
        parent::tearDown();

        // Reset testing datetime
        Carbon::setTestNow();
    }

    protected function getObjectMock($methods = [], $constructorArgs = [])
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

        $property->setValue($object, $value);
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
