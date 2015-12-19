<?php
namespace TmlpStats\Tests\Validate;

use stdClass;
use TmlpStats\Validate\NullValidator;
use TmlpStats\Validate\ValidatorFactory;
use TmlpStats\Validate\Objects;
use TmlpStats\Validate\Relationships;

class ValidatorFactoryTest extends \TmlpStats\Tests\TestAbstract
{
    protected $testClass = ValidatorFactory::class;

    /**
     * @dataProvider providerBuild
     */
    public function testBuild($statsReport, $type, $expectedClass)
    {
        if ($expectedClass == 'Exception') {
            $this->setExpectedException(
                'Exception', 'Invalid type passed to ValidatorFactory'
            );
        }
        $validator = ValidatorFactory::build($statsReport, $type);

        $this->assertInstanceOf($expectedClass, $validator);
    }

    public function providerBuild()
    {
        $statsReport = new stdClass;

        return [
            [$statsReport, 'centerStats', Objects\CenterStatsValidator::class],
            [$statsReport, 'tmlpRegistration', Objects\TmlpRegistrationValidator::class],
            [$statsReport, 'classList', Objects\ClassListValidator::class],
            [$statsReport, 'contactInfo', Objects\ContactInfoValidator::class],
            [$statsReport, 'commCourseInfo', Objects\CommCourseInfoValidator::class],
            [$statsReport, 'tmlpCourseInfo', Objects\TmlpCourseInfoValidator::class],
            [$statsReport, 'statsReport', Objects\StatsReportValidator::class],
            [$statsReport, 'committedTeamMember', Relationships\CommittedTeamMemberValidator::class],
            [$statsReport, 'contactInfoTeamMember', Relationships\ContactInfoTeamMemberValidator::class],
            [$statsReport, 'duplicateTeamMember', Relationships\DuplicateTeamMemberValidator::class],
            [$statsReport, 'duplicateTmlpRegistration', Relationships\DuplicateTmlpRegistrationValidator::class],
            [$statsReport, 'teamExpansion', Relationships\TeamExpansionValidator::class],
            [$statsReport, 'centerGames', Relationships\CenterGamesValidator::class],
            [$statsReport, null, NullValidator::class],
            [$statsReport, 'asdf', 'Exception'],
        ];
    }
}
