<?php
namespace TmlpStats\Tests\Validate\Objects;

use Carbon\Carbon;
use stdClass;
use TmlpStats\Tests\Traits\MocksMessages;
use TmlpStats\Tests\Validate\ValidatorTestAbstract;
use TmlpStats\Validate\Objects\StatsReportValidator;

class StatsReportValidatorTest extends ValidatorTestAbstract
{
    use MocksMessages;

    protected $testClass = StatsReportValidator::class;

    /**
     * @dataProvider providerValidate
     */
    public function testValidate($data, $statsReport, $messages, $expectedResult)
    {
        $validator = $this->getObjectMock([
            'addMessage',
        ], [$statsReport]);

        $this->setupMessageMocks($validator, $messages);

        $result = $validator->run($data);

        $this->assertEquals($expectedResult, $result);
    }

    public function providerValidate()
    {
        $statsReport                = new stdClass;
        $statsReport->reportingDate = Carbon::createFromDate(2015, 12, 18);

        $statsReport->quarter                 = new stdClass;
        $statsReport->quarter->endWeekendDate = Carbon::createFromDate(2016, 02, 19);

        $statsReport->center               = new stdClass;
        $statsReport->center->sheetVersion = '15.4.2';

        $statsReportLastWeek                = clone $statsReport;
        $statsReportLastWeek->reportingDate = Carbon::createFromDate(2016, 02, 17);


        $statsReportCorrectLastWeek                = clone $statsReport;
        $statsReportCorrectLastWeek->reportingDate = Carbon::createFromDate(2016, 02, 19);

        return [
            // Success Case
            [
                [
                    'expectedVersion' => '15.4.2',
                    'expectedDate'    => $statsReport->reportingDate,
                ],
                $statsReport,
                [],
                true,
            ],
            // Ignored version (null)
            [
                [
                    'expectedVersion' => null,
                    'expectedDate'    => $statsReport->reportingDate,
                ],
                $statsReport,
                [],
                true,
            ],
            // Ignored version (not set)
            [
                [
                    'expectedDate'    => $statsReport->reportingDate,
                ],
                $statsReport,
                [],
                true,
            ],
            // Invalid Version
            [
                [
                    'expectedVersion' => '15.4.3',
                    'expectedDate'    => $statsReport->reportingDate,
                ],
                $statsReport,
                [
                    ['IMPORTDOC_SPREADSHEET_VERSION_MISMATCH', '15.4.3', '15.4.2'],
                ],
                false,
            ],
            // Ignored date (null)
            [
                [
                    'expectedVersion' => '15.4.2',
                    'expectedDate'    => null,
                ],
                $statsReport,
                [],
                true,
            ],
            // Ignored date (not set)
            [
                [
                    'expectedVersion' => '15.4.2',
                ],
                $statsReport,
                [],
                true,
            ],
            // Invalid date
            [
                [
                    'expectedVersion' => '15.4.2',
                    'expectedDate'    => Carbon::createFromDate(2015, 12, 11),
                ],
                $statsReport,
                [
                    ['IMPORTDOC_SPREADSHEET_DATE_MISMATCH', '2015-12-18', '2015-12-11'],
                ],
                false,
            ],
            // Invalid date last week of quarter
            [
                [
                    'expectedVersion' => '15.4.2',
                    'expectedDate'    => $statsReportLastWeek->quarter->endWeekendDate,
                ],
                $statsReportLastWeek,
                [
                    ['IMPORTDOC_SPREADSHEET_DATE_MISMATCH_LAST_WEEK', '2016-02-17', '2016-02-19'],
                ],
                false,
            ],
            // Valid date last week of quarter
            [
                [
                    'expectedVersion' => '15.4.2',
                    'expectedDate'    => $statsReportCorrectLastWeek->quarter->endWeekendDate,
                ],
                $statsReportCorrectLastWeek,
                [],
                true,
            ],
        ];
    }
}
