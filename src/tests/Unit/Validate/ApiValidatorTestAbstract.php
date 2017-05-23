<?php
namespace TmlpStats\Tests\Unit\Validate;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Debug\Dumper;
use TmlpStats as Models;
use TmlpStats\Api\Parsers;
use TmlpStats\Tests\Unit\Traits;

class ApiValidatorTestAbstract extends ValidatorTestAbstract
{
    use Traits\MocksQuarters, Traits\MocksModel;

    protected $defaultObjectMethods = [];

    protected $instantiateApp = true;

    protected $dataTemplate = [];

    protected $messageTemplate = [
        'id' => 'placeholder',
        'level' => 'error',
        'reference' => [
            'id' => null,
            'type' => 'placeholder',
        ],
    ];

    public function setUp()
    {
        parent::setUp();

        $this->addModelParserMock(Parsers\CenterParser::class, models\Center::class);
        $this->addModelParserMock(Parsers\QuarterParser::class, Models\Quarter::class);
        $this->addModelParserMock(Parsers\WithdrawCodeParser::class);
        $this->addModelParserMock(Parsers\TeamMemberParser::class);
        $this->addModelParserMock(Parsers\ApplicationParser::class);

        $this->reportingDate = Carbon::parse('2016-09-02');

        $this->nextQuarter = $this->getModelMock();
        $this->futureQuarter = $this->getModelMock();

        $this->statsReport = new \stdClass();
        $this->statsReport->reportingDate = $this->reportingDate;
        $this->statsReport->center = new Models\Center();
        $this->statsReport->center->regionId = 123;
        $this->statsReport->center->region = new Models\Region(['id' => 123]);
        $dates = [
            'startWeekendDate' => Carbon::parse('2016-08-19')->startOfDay(),
            'classroom1Date' => Carbon::parse('2016-09-09')->startOfDay(),
            'classroom2Date' => Carbon::parse('2016-09-30')->startOfDay(),
            'classroom3Date' => Carbon::parse('2016-10-28')->startOfDay(),
            'endWeekendDate' => Carbon::parse('2016-11-18')->startOfDay(),
        ];
        $this->statsReport->quarter = $this->getQuarterMock([], array_merge(['nextQuarter' => $this->nextQuarter], $dates));
        $this->mockCenterQuarter();
        $this->mockRegionQuarter($dates);
    }

    protected function addModelParserMock($parserClass, $modelClass = Model::class)
    {

        $that = $this;
        $parser = $this->getMockBuilder($parserClass)
                       ->setMethods(['fetch'])
                       ->getMock();

        $parser->expects($this->any())
               ->method('fetch')
               ->will($this->returnCallback(function ($class, $id) use ($that, $modelClass) {
                    $mock = $that->getModelMock([], [], $modelClass);
                    $mock->id = $id;

                    return $mock;
                }));

        $this->app->bind($parserClass, function ($app) use ($parser) {
            return $parser;
        });
    }

    protected function assertMessages($expected, $actual)
    {
        if (count($expected) != count($actual)) {
            $dumper = new Dumper();
            $dumper->dump($expected);
            $dumper->dump($actual);
        }
        $this->assertEquals(count($expected), count($actual), 'Number of messages do not match');

        foreach ($expected as $idx => $expectedMessage) {
            $message = $actual[$idx]->toArray();

            // Allow assertions that don't look at message field
            if (!isset($expectedMessage['message'])) {
                unset($message['message']);
            }
            if (!isset($expectedMessage['reference']['flattened'])) {
                unset($message['reference']['flattened']);
            }

            $this->assertEquals($expectedMessage, $message);
        }
    }

    public function getMessageData($template, $updates)
    {
        foreach ($updates as $dotKey => $value) {
            array_set($template, $dotKey, $value);
        }

        return $template;
    }

    public function getObjectMock($methods = [], $constructorArgs = [])
    {
        // If there's nothing to mock, return a real object
        if (!$methods && !$this->defaultObjectMethods) {
            $report = $constructorArgs ? $constructorArgs[0] : $this->statsReport;

            return new $this->testClass($report);
        }

        $methods = array_unique(array_merge($this->defaultObjectMethods, $methods));

        if (!$constructorArgs) {
            $constructorArgs = [$this->statsReport];
        }

        return parent::getObjectMock($methods, $constructorArgs);
    }
}
