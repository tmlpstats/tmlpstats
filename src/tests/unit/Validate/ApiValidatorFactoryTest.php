<?php
namespace TmlpStats\Tests\Unit\Validate;

use stdClass;
use TmlpStats\Validate\NullValidator;
use TmlpStats\Validate\ApiValidatorFactory;
use TmlpStats\Validate\Objects;
use TmlpStats\Validate\Relationships;
use TmlpStats\Validate\Differences;

class ApiValidatorFactoryTest extends \TmlpStats\Tests\TestAbstract
{
    protected $testClass = ApiValidatorFactory::class;

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
        $validator = ApiValidatorFactory::build($statsReport, $type);

        $this->assertInstanceOf($expectedClass, $validator);
    }

    public function providerBuild()
    {
        $statsReport = new stdClass;

        return [
            [$statsReport, 'Course', Objects\ApiCourseValidator::class],
            [$statsReport, 'Scoreboard', Objects\ApiScoreboardValidator::class],
            [$statsReport, 'TeamApplication', Objects\ApiTeamApplicationValidator::class],
            [$statsReport, 'TeamMember', Objects\ApiTeamMemberValidator::class],
            [$statsReport, 'Accountability', Relationships\ApiAccountabilityValidator::class],
            [$statsReport, 'CenterGames', Relationships\ApiCenterGamesValidator::class],
            [$statsReport, 'CourseChange', Differences\ApiCourseChangeValidator::class],
            [$statsReport, 'TeamApplicationChange', Differences\ApiTeamApplicationChangeValidator::class],
            [$statsReport, 'null', NullValidator::class],
            [$statsReport, 'asdf', 'Exception'],
        ];
    }
}
