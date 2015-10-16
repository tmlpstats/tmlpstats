<?php
namespace TmlpStats\Tests\Validate;

use stdClass;

use TmlpStats\Validate\ValidatorFactory;

class ValidatorFactoryTest extends \TmlpStats\Tests\TestAbstract
{
    protected $testClass = 'TmlpStats\Validate\ValidatorFactory';

    public function testBuildReturnsCenterStatsValidator()
    {
        $class = 'TmlpStats\Validate\CenterStatsValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'centerStats');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpRegistrationValidator()
    {
        $class = 'TmlpStats\Validate\TmlpRegistrationValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'tmlpRegistration');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsClassListValidator()
    {
        $class = 'TmlpStats\Validate\ClassListValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'classList');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsContactInfoValidator()
    {
        $class = 'TmlpStats\Validate\ContactInfoValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'contactInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsCommCourseInfoValidator()
    {
        $class = 'TmlpStats\Validate\CommCourseInfoValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'commCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpCourseInfoValidator()
    {
        $class = 'TmlpStats\Validate\TmlpCourseInfoValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'tmlpCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidator()
    {
        $class = 'TmlpStats\Validate\NullValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'null');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidatorWhenNoTypeProvided()
    {
        $class = 'TmlpStats\Validate\NullValidator';
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport);

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildThrowsExceptionForInvalidType()
    {
        $this->setExpectedException(
            'Exception', 'Invalid type passed to ValidatorFactory'
        );
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'invalidType');
    }
}
