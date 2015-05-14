<?php
namespace TmlpStatsTests\Validate;

use TmlpStats\Validate\ValidatorFactory;

class ValidatorFactoryTest extends \TmlpStatsTests\TestAbstract
{
    protected $testClass = 'TmlpStats\Validate\ValidatorFactory';

    public function testBuildReturnsCenterStatsValidator()
    {
        $class = 'TmlpStats\Validate\CenterStatsValidator';

        $validator = ValidatorFactory::build('11', 'centerStats');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpRegistrationValidator()
    {
        $class = 'TmlpStats\Validate\TmlpRegistrationValidator';

        $validator = ValidatorFactory::build('11', 'tmlpRegistration');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsClassListValidator()
    {
        $class = 'TmlpStats\Validate\ClassListValidator';

        $validator = ValidatorFactory::build('11', 'classList');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsContactInfoValidator()
    {
        $class = 'TmlpStats\Validate\ContactInfoValidator';

        $validator = ValidatorFactory::build('11', 'contactInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsCommCourseInfoValidator()
    {
        $class = 'TmlpStats\Validate\CommCourseInfoValidator';

        $validator = ValidatorFactory::build('11', 'commCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpCourseInfoValidator()
    {
        $class = 'TmlpStats\Validate\TmlpCourseInfoValidator';

        $validator = ValidatorFactory::build('11', 'tmlpCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidator()
    {
        $class = 'TmlpStats\Validate\NullValidator';

        $validator = ValidatorFactory::build('11', 'null');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidatorWhenNoTypeProvided()
    {
        $class = 'TmlpStats\Validate\NullValidator';

        $validator = ValidatorFactory::build('11');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildThrowsExceptionForInvalidType()
    {
        $this->setExpectedException(
            'Exception', 'Invalid type passed to ValidatorFactory'
        );

        $validator = ValidatorFactory::build('11', 'invalidType');
    }
}
