<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Domain;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;
use TmlpStats\Tests\Mocks\MockContext;

class ScoreboardTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();

        $this->center = Models\Center::abbreviation('VAN')->first();
        $this->quarter = Models\Quarter::year(2016)->quarterNumber(1)->first();

        $this->report = Models\StatsReport::firstOrCreate([
            'center_id' => $this->center->id,
            'quarter_id' => $this->quarter->id,
            'reporting_date' => '2016-04-15',
            'submitted_at' => null,
            'version' => 'test',
        ]);

        $this->now = Carbon::parse('2016-04-15 18:45:00');
        Carbon::setTestNow($this->now);
    }

    /**
     * @dataProvider providerStash
     */
    public function testStash($reportingDate)
    {
        $reportingDateString = $this->report->reportingDate->toDateString();
        $parameters = [
            'method' => 'Submission.Scoreboard.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'week' => $reportingDateString,
                'promise' => [
                    'cap' => 0,
                    'cpc' => 1,
                    't1x' => 2,
                    't2x' => 3,
                    'gitw' => 4,
                    'lf' => 5,
                ],
                'actual' => [
                    'cap' => 0,
                    'cpc' => 0,
                    't1x' => 1,
                    't2x' => 2,
                    'gitw' => 3,
                    'lf' => 4,
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'valid' => false, // false because we don't bother to create the necessary objects to make scores accurate
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);

        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Scoreboard::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(0, $result->game('cap')->promise());
        $this->assertEquals(1, $result->game('cpc')->promise());
        $this->assertEquals(2, $result->game('t1x')->promise());
        $this->assertEquals(3, $result->game('t2x')->promise());
        $this->assertEquals(4, $result->game('gitw')->promise());
        $this->assertEquals(5, $result->game('lf')->promise());
        $this->assertEquals(0, $result->game('cap')->actual());
        $this->assertEquals(0, $result->game('cpc')->actual());
        $this->assertEquals(1, $result->game('t1x')->actual());
        $this->assertEquals(2, $result->game('t2x')->actual());
        $this->assertEquals(3, $result->game('gitw')->actual());
        $this->assertEquals(4, $result->game('lf')->actual());
    }

    public function providerStash()
    {
        return [
            ['2016-04-22'], // Non-existent report
            ['2016-04-15'], // Existing report
        ];
    }

    public function testStashFailsValidation()
    {
        $reportingDate = '2016-04-15';

        $reportingDateString = $this->report->reportingDate->toDateString();
        $parameters = [
            'method' => 'Submission.Scoreboard.stash',
            'center' => $this->center->abbreviation,
            'reportingDate' => $reportingDate,
            'data' => [
                'week' => $reportingDateString,
                'promise' => [
                    'cap' => 0,
                    'cpc' => 1,
                    't1x' => 2,
                    't2x' => 3,
                    'gitw' => 4,
                    'lf' => 5,
                ],
            ],
        ];

        $expectedResponse = [
            'success' => true,
            'valid' => false,
        ];

        $this->post('/api', $parameters)->seeJsonHas($expectedResponse);

        $result1 = App::make(Api\SubmissionData::class)->allForType($this->center, new Carbon($reportingDate), Domain\Scoreboard::class);

        $this->assertEquals(1, count($result1));
        $result = $result1[0];
        $this->assertEquals(0, $result->game('cap')->promise());
        $this->assertEquals(1, $result->game('cpc')->promise());
        $this->assertEquals(2, $result->game('t1x')->promise());
        $this->assertEquals(3, $result->game('t2x')->promise());
        $this->assertEquals(4, $result->game('gitw')->promise());
        $this->assertEquals(5, $result->game('lf')->promise());
        $this->assertEquals(null, $result->game('cap')->actual());
        $this->assertEquals(null, $result->game('cpc')->actual());
        $this->assertEquals(null, $result->game('t1x')->actual());
        $this->assertEquals(null, $result->game('t2x')->actual());
        $this->assertEquals(null, $result->game('gitw')->actual());
        $this->assertEquals(null, $result->game('lf')->actual());
    }

    /**
     * @dataProvider providerApiThrowsExceptionForInvalidDate
     */
    public function testApiThrowsExceptionForInvalidDate($method, $data = null)
    {
        $reportingDate = Carbon::parse('this thursday', $this->center->timezone)
            ->startOfDay()
            ->toDateString();

        $parameters = [
            'method' => $method,
            'reportingDate' => $reportingDate,
            'center' => $this->center->id,
        ];

        if ($data !== null) {
            $parameters['data'] = $data;
        }

        $expectedResponse = [
            'success' => false,
            'error' => [
                'message' => 'Reporting date must be a Friday.',
            ],
        ];

        $headers = ['Accept' => 'application/json'];
        $this->post('/api', $parameters, $headers)->seeJsonHas($expectedResponse);
    }

    public function providerApiThrowsExceptionForInvalidDate()
    {
        return [
            ['Submission.Scoreboard.allForCenter'],
            ['Submission.Scoreboard.stash', []],
        ];
    }

    /**
     * @dataProvider providerAllForCenter
     */
    public function testAllForCenter($input)
    {
        $reportingDate = $this->report->reportingDate;
        $locks = $this->buildLocks();
        $context = MockContext::defaults()
            ->withUser($this->user)
            ->withFakedAdmin()
            ->withSetting('scoreboardLock', $locks[$input['locks']]->toArray())
            ->install();
        $api = App::make(Api\Submission\Scoreboard::class);

        if ($csds = array_get($input, 'csds', null)) {
            $this->fillCsds($csds, $reportingDate);
        }

        // Now fill in stashes
        foreach ($input['stashes'] as $toStash) {
            App::make(Api\SubmissionData::class)->store($this->center, $reportingDate, Domain\Scoreboard::fromArray($toStash));
        }

        // Do the API thing
        $result = $api->allForCenter($this->center, $reportingDate, array_get($input, 'includeInProgress', true), true);

        foreach ($input['assertions'] as $weekStr => $assertions) {
            $d = Carbon::parse($weekStr);
            $week = $result->getWeek($d);

            if ($checkMeta = array_get($assertions, 'meta', null)) {
                foreach ($checkMeta as $k => $v) {
                    $vv = print_r($v, true);
                    $this->assertEquals($v, array_get($week->meta, $k, 'UNDEFINED'), "$weekStr: expected meta $k to equal {$vv}");
                }
            }
            if ($gamesPop = array_get($assertions, 'games', null)) {
                foreach ($gamesPop as $k => $v) {
                    list($gameKey, $type) = explode('.', $k);
                    $this->assertEquals($v, $week->game($gameKey)->$type(), "$weekStr: expected $gameKey $type to equal $v");
                }
            }
        }
    }

    public function providerAllForCenter()
    {
        $april15 = [
            'week' => '2016-04-15',
            'games' => [
                'cap' => ['promise' => 0, 'actual' => 0],
                'cpc' => ['promise' => 1, 'actual' => 1],
                't1x' => ['promise' => 2, 'actual' => 2],
                't2x' => ['promise' => 3, 'actual' => 3],
                'gitw' => ['promise' => 100, 'actual' => 100],
                'lf' => ['promise' => 10, 'actual' => 10],
            ],
        ];

        $columns = ['posted_date', 'reporting_date', 'type', 'cap', 'cpc', 't1x', 't2x', 'gitw', 'lf'];
        $csds = [
            array_combine($columns, ['2016-02-26', '2016-02-26', 'promise', 1, 1, 1, 1, 1, 85]),
            array_combine($columns, ['2016-02-26', '2016-03-04', 'promise', 2, 2, 2, 2, 2, 85]),
            array_combine($columns, ['2016-02-26', '2016-03-11', 'promise', 3, 3, 3, 3, 3, 85]),
            array_combine($columns, ['2016-02-26', '2016-03-18', 'promise', 4, 4, 4, 4, 4, 85]),
            array_combine($columns, ['2016-02-26', '2016-04-15', 'promise', 7, 7, 7, 7, 7, 85]),
        ];

        return [
            [[
                'includeInProgress' => true,
                'locks' => 'normal',
                'stashes' => [$april15],
                'csds' => $csds,
                'assertions' => [
                    '2016-02-26' => [
                        'meta' => [
                            'canEditPromise' => false,
                            'canEditActual' => false,
                        ],
                    ],
                    '2016-03-18' => [
                        'meta' => [
                            'isClassroom' => true,
                        ],
                        'games' => [
                            't1x.promise' => 4,
                        ],
                    ],
                    '2016-04-15' => [
                        'meta' => [
                            'canEditPromise' => false,
                            'canEditActual' => true,
                            'localChanges' => true,
                            'mergedLocal' => true,
                        ],
                        'games' => [
                            // This combination proves that we merged local changes with official promises.
                            't1x.promise' => 7,
                            't1x.actual' => 2,
                            't2x.promise' => 7,
                            't2x.actual' => 3,
                        ],
                    ],
                ],
            ]],

            [[
                'includeInProgress' => true,
                'locks' => 'halfLocked',
                'stashes' => [$april15],
                'csds' => $csds,
                'assertions' => [
                    '2016-02-26' => [
                        'meta' => [
                            'canEditPromise' => false,
                            'canEditActual' => false,
                        ],
                        'games' => [
                            'cap.promise' => 1,
                        ],
                    ],
                    '2016-04-15' => [
                        'meta' => [
                            'canEditPromise' => true,
                            'canEditActual' => true,
                            'localChanges' => true,
                            'mergedLocal' => 'UNDEFINED',
                        ],
                        'games' => [
                            // This combination proves that editable promises works
                            't1x.promise' => 2,
                            't1x.actual' => 2,
                            't2x.promise' => 3,
                        ],
                    ],
                ],
            ]],
        ];
    }

    public function buildLocks($quarter = null)
    {
        $cq = Domain\CenterQuarter::ensure($this->center, $quarter ?: $this->quarter);
        $reportingDates = $cq->listReportingDates();
        $normal = new Domain\ScoreboardLockQuarter($reportingDates);
        $unlocked = new Domain\ScoreboardLockQuarter($reportingDates);
        $halfLocked = new Domain\ScoreboardLockQuarter($reportingDates); // unlocked after CR2
        foreach ($reportingDates as $d) {
            $unlocked->getWeek($d)->editPromise = true;
            $halfLocked->getWeek($d)->editPromise = ($d->gte($cq->classroom2Date));
        }

        return compact('normal', 'unlocked', 'halfLocked');
    }

    protected function fillCsds($input, $defaultReportingDate)
    {
        $createdReports = [];
        foreach ($input as $csdInput) {
            $csdInput['tdo'] = array_get($csdInput, 'tdo', 100);
            if (isset($csdInput['posted_date'])) {
                $report = array_get($createdReports, $csdInput['posted_date'], null);
                if ($report === null) {
                    $report = Models\StatsReport::firstOrNew([
                        'center_id' => $this->center->id,
                        'quarter_id' => $this->quarter->id,
                        'reporting_date' => $csdInput['posted_date'],
                        'version' => 'test',
                    ]);
                    if (!$report->id) {
                        $report->save();
                        $gr = Models\GlobalReport::firstOrCreate(['reporting_date' => $csdInput['posted_date']]);
                        $gr->addCenterReport($report);
                    }
                    $createdReports[$csdInput['posted_date']] = $report;
                }
                $csdInput['stats_report_id'] = $report->id;
                unset($csdInput['posted_date']);
            } else {
                $csdInput['stats_report_id'] = array_get($csdInput, 'stats_report_id', $this->report->id);
            }
            Models\CenterStatsData::create($csdInput);
        }
    }
}
