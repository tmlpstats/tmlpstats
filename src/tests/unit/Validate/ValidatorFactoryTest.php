<?php
namespace TmlpStats\Tests\Validate;

use stdClass;
use TmlpStats\Validate\NullValidator;
use TmlpStats\Validate\ValidatorFactory;
use TmlpStats\Validate\Objects;

class ValidatorFactoryTest extends \TmlpStats\Tests\TestAbstract
{
    protected $testClass = ValidatorFactory::class;

    public function testBuildReturnsCenterStatsValidator()
    {
        $class       = Objects\CenterStatsValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'centerStats');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpRegistrationValidator()
    {
        $class       = Objects\TmlpRegistrationValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'tmlpRegistration');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsClassListValidator()
    {
        $class       = Objects\ClassListValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'classList');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsContactInfoValidator()
    {
        $class       = Objects\ContactInfoValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'contactInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsCommCourseInfoValidator()
    {
        $class       = Objects\CommCourseInfoValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'commCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsTmlpCourseInfoValidator()
    {
        $class       = Objects\TmlpCourseInfoValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'tmlpCourseInfo');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidator()
    {
        $class       = NullValidator::class;
        $statsReport = new stdClass;

        $validator = ValidatorFactory::build($statsReport, 'null');

        $this->assertInstanceOf($class, $validator);
    }

    public function testBuildReturnsNullValidatorWhenNoTypeProvided()
    {
        $class       = NullValidator::class;
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
