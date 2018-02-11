<?php
namespace TmlpStats\Tests\Unit\Traits;

use Illuminate\Database\Eloquent\Model;

trait MocksModel
{
    /**
     * Get a Quarter object mock
     *
     * Getters are mocked for all fields provided in $data
     *
     * @param array $methods
     * @param array $data
     *
     * @return mixed
     */
    protected function getModelMock($methods = [], $data = [], $baseClass = Model::class)
    {
        static $idOffset = 0;

        // We don't really need to mock this, but if nothing is mocked, then
        // everthing is mocked. Thanks phpunit.
        $defaultMethods = ['unguard'];

        $methods = $this->mergeMockMethods($defaultMethods, $methods);

        $model = $this->getMockBuilder($baseClass)
                      ->setMethods($methods)
                      ->getMock();

        static::$idOffset++;
        $model->id = static::$idOffset;

        return $model;
    }
}
