<?php
namespace TmlpStats\Tests\Functional\Api;

use App;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use TmlpStats as Models;
use TmlpStats\Api;
use TmlpStats\Tests\Functional\FunctionalTestAbstract;

class ContextTest extends FunctionalTestAbstract
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    protected $instantiateApp = true;
    protected $runMigrations = true;
    protected $runSeeds = true;

    public function setUp()
    {
        parent::setUp();

        $this->now = Carbon::parse('2016-04-15 18:45:00');
        Carbon::setTestNow($this->now);
    }

    /**
     * @dataProvider providerGetSetting
     */
    public function testGetSetting($input)
    {
        foreach ($input['initSettings'] as $row) {
            $setting = new Models\Setting();
            $setting->active = true;
            foreach ($row as $k => $v) {
                switch ($k) {
                    case 'center':
                        $setting->centerId = Models\Center::abbreviation(strtoupper($v))->firstOrFail()->id;
                        break;
                    case 'quarter':
                        $setting->quarterId = Models\Quarter::year(2016)->quarterNumber($v)->firstOrFail()->id;
                        break;
                    case 'region':
                        $setting->regionId = Models\Region::where('abbreviation', 'like', $v)->firstOrFail()->id;
                        break;
                    default:
                        $setting->$k = $v;
                        break;
                }
            }
            $setting->save();
        }
        $context = App::make(Api\Context::class);
        $scopeParams = $input['scopeParams'];
        if (count($scopeParams) >= 1) {
            $scopeParams[0] = Models\Center::abbreviation(strToUpper($scopeParams[0]))->first() ?: Models\Region::where('abbreviation', 'like', $scopeParams[0])->firstOrFail();
        }
        if (count($scopeParams) >= 2) {
            $scopeParams[1] = Models\Quarter::year(2016)->quarterNumber($scopeParams[1])->firstOrFail();
        }
        foreach ($input['tests'] as $idx => $testCase) {
            $inParams = array_merge([$testCase['name']], $scopeParams);

            $result = call_user_func_array([$context, 'getSetting'], $inParams);
            $this->assertEquals($testCase['expect'], $result, "Checking tests array #{$idx} for setting {$testCase['name']}::");
        }
    }

    public function providerGetSetting()
    {
        $baseSettingHierarchy = [
            ['center' => 'van', 'quarter' => 1, 'name' => 'someSetting', 'value' => json_encode('C1Q1')],
            ['center' => 'van', 'name' => 'someSetting2', 'value' => json_encode('C1')],
            ['region' => 'west', 'name' => 'someSetting3', 'value' => json_encode('west1')],
            ['region' => 'west', 'quarter' => 1, 'name' => 'someSetting3', 'value' => json_encode('west1q1')],
            ['region' => 'west', 'quarter' => 2, 'name' => 'someSetting3', 'value' => json_encode('west1q2')],
            ['region' => 'na', 'name' => 'someSetting4', 'value' => json_encode('na1')],
            ['region' => 'na', 'quarter' => 1, 'name' => 'someSetting4', 'value' => json_encode('na1q1')],
            ['name' => 'someSetting5', 'value' => json_encode('Global')],
            ['center' => 'bos', 'quarter' => 1, 'name' => 'someSetting', 'value' => json_encode('bostonq1')],
            ['region' => 'east', 'quarter' => 1, 'name' => 'someSetting3', 'value' => json_encode('east1q1')],
        ];

        return [
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['van', 1],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => 'C1Q1'],
                    ['name' => 'someSetting2', 'expect' => 'C1'],
                    ['name' => 'someSetting3', 'expect' => 'west1q1'],
                    ['name' => 'someSetting4', 'expect' => 'na1q1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['van'],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => 'C1'],
                    ['name' => 'someSetting3', 'expect' => 'west1'],
                    ['name' => 'someSetting4', 'expect' => 'na1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => [],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => null],
                    ['name' => 'someSetting3', 'expect' => null],
                    ['name' => 'someSetting4', 'expect' => null],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['van', 2],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => 'C1'],
                    ['name' => 'someSetting3', 'expect' => 'west1q2'],
                    ['name' => 'someSetting4', 'expect' => 'na1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['bos', 1],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => 'bostonq1'],
                    ['name' => 'someSetting2', 'expect' => null],
                    ['name' => 'someSetting3', 'expect' => 'east1q1'],
                    ['name' => 'someSetting4', 'expect' => 'na1q1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['bos', 2],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => null],
                    ['name' => 'someSetting3', 'expect' => null],
                    ['name' => 'someSetting4', 'expect' => 'na1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],

            // Testing regions
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['east', 1],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => null],
                    ['name' => 'someSetting3', 'expect' => 'east1q1'],
                    ['name' => 'someSetting4', 'expect' => 'na1q1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],
            [[
                'initSettings' => $baseSettingHierarchy,
                'scopeParams' => ['east'],
                'tests' => [
                    ['name' => 'unknown', 'expect' => null],
                    ['name' => 'someSetting', 'expect' => null],
                    ['name' => 'someSetting2', 'expect' => null],
                    ['name' => 'someSetting3', 'expect' => null],
                    ['name' => 'someSetting4', 'expect' => 'na1'],
                    ['name' => 'someSetting5', 'expect' => 'Global'],
                ],
            ]],

            ///////// After here, we're testing more of the JSON decoding and not the scopes.
            [[
                'initSettings' => [
                    ['center' => 'la', 'name' => 'ordered', 'value' => json_encode([1, 2, 3, 4])],
                    ['center' => 'la', 'name' => 'bareString', 'value' => 'bareString'],
                    ['center' => 'la', 'name' => 'kv', 'value' => json_encode(['a' => 1, 'b' => 2])],
                ],
                'scopeParams' => ['la'],
                'tests' => [
                    ['name' => 'ordered', 'expect' => [1, 2, 3, 4]],
                    ['name' => 'bareString', 'expect' => 'bareString'],
                    ['name' => 'kv', 'expect' => ['a' => 1, 'b' => 2]],
                ],

            ]],
        ];
    }

}
