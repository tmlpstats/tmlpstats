<?php
namespace TmlpStats\Tests\Unit;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Center;
use TmlpStats\Import\ImportManager;
use TmlpStats\Person;
use TmlpStats\Tests\TestAbstract;
use TmlpStats\Tests\Unit\Traits\MocksQuarters;
use TmlpStats\Tests\Unit\Traits\MocksSettings;

class ImportManagerTest extends TestAbstract
{
    use MocksSettings, MocksQuarters;

    protected $testClass = ImportManager::class;

    /**
     * @dataProvider providerGetEmail
     */
    public function testGetEmail($person, $expectedResult)
    {
        $result = ImportManager::getEmail($person);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerGetEmail()
    {
        $data = [];

        // No person provided, should return null
        $data[] = [null, null];

        // Person is unsubscribed, should return null
        $person = new Person([
            'email'        => 'test@tmlpstats.com',
            'unsubscribed' => true,
        ]);
        $data[] = [$person, null];

        // Person is not unsubscribed, should return email
        $person               = clone $person;
        $person->unsubscribed = false;
        $data[]               = [$person, 'test@tmlpstats.com'];

        return $data;
    }
}
