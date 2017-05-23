<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Domain;

/**
 * Applications
 */
class SubmissionData extends AuthenticatedApiBase
{
    // Defines the type mappings recognized by this storage engine
    // Looks like stored type => { class, id attr }
    protected static $mappedTypes = [
        [
            'key' => 'application',
            'class' => Domain\TeamApplication::class,
            'idAttr' => 'id',
        ],
        [
            'key' => 'course',
            'class' => Domain\Course::class,
            'idAttr' => 'id',
        ],
        [
            'key' => 'scoreboard_week',
            'class' => Domain\Scoreboard::class,
            'idAttr' => 'week',
            'toArray' => 'toNewArray',
        ],
        [
            'key' => 'team_member',
            'class' => Domain\TeamMember::class,
            'idAttr' => 'id',
        ],
        [
            'key' => 'next_qtr_accountability',
            'class' => Domain\NextQtrAccountability::class,
            'idAttr' => 'id',
        ],
        [
            'key' => 'program_leader',
            'class' => Domain\ProgramLeader::class,
            'idAttr' => 'id',
        ],
    ];

    protected $keyTypeMapping = [];
    protected $combinedTypeMapping = [];

    // Make a new SubmissionData API. Since this class is a singleton in the App constructor,
    // we know that the two mappings will only get made once.
    public function __construct(Context $context)
    {
        parent::__construct($context);

        foreach (static::$mappedTypes as $className => $v) {
            $this->keyTypeMapping[$v['key']] = $v;
            $this->combinedTypeMapping[$v['key']] = $v;
            $this->combinedTypeMapping[$v['class']] = $v;
        }
    }

    /**
     * Return all objects for a given type.
     * @param  Models\Center $center        A center, naturally
     * @param  Carbon        $reportingDate Reporting date
     * @param  string        $type          either the class or key representing a type.
     * @return A collection of objects mapped to the proxied type.
     */
    public function allForType(Models\Center $center, Carbon $reportingDate, $type)
    {
        $conf = $this->combinedTypeMapping[$type];

        $result = Models\SubmissionData::centerDate($center, $reportingDate)->type($conf['key'])->get();

        return $this->fulfillQuery($result, $conf);
    }

    /**
     * A more efficient way of getting all stashes for a whole quarter
     * @param  Domain\CenterQuarter $centerQuarter [description]
     * @param  [type]               $type          [description]
     * @param  array                $options       [description]
     */
    public function allForTypeWholeQuarter(Domain\CenterQuarter $centerQuarter, $type, $options = [])
    {
        $reverse = array_get($options, 'reverse', false);
        $conf = $this->combinedTypeMapping[$type];
        $result = Models\SubmissionData::type($conf['key'])
            ->center($centerQuarter->center)
            ->where('reporting_date', '>=', $centerQuarter->firstWeekDate)
            ->where('reporting_date', '<=', $centerQuarter->endWeekendDate)
            ->orderBy('reporting_date', ($reverse) ? 'desc' : 'asc');

        return $this->fulfilLQuery($result->get(), $conf);

    }

    /**
     * Helper to fulfill a query
     * @param  [type] $result Iterable result set from laravel.
     * @param  array  $conf   Config
     * @return [type]         [description]
     */
    protected function fulfillQuery($result, array $conf)
    {
        $className = $conf['class'];

        return $result->map(function ($row) use ($className) {
            return $className::fromArray($row->data);
        });
    }

    /**
     * Get a single SubmissionData from the store.
     * @param  Models\Center $center        [description]
     * @param  Carbon        $reportingDate [description]
     * @param  string        $type          [description]
     * @param  string        $id            [description]
     * @return [type]                       [description]
     */
    public function get(Models\Center $center, Carbon $reportingDate, $type, $id)
    {
        $conf = $this->combinedTypeMapping[$type];
        $className = $conf['class'];

        $result = Models\SubmissionData::centerDate($center, $reportingDate)->type($conf['key'])->storedId($id)->get();
        if ($result != null) {
            return $className::fromArray($row->data);
        }
    }

    /**
     * Store a single Submission Data entry, also logging the save operation.
     * @param  Center $center        Center to scope by
     * @param  Carbon $reportingDate Reporting date
     * @param  Any    $obj           A domain object that we're going to marshal. This object
     *                               must be known to the store so we can effectively marshal it.
     * @return null
     */
    public function store(Models\Center $center, Carbon $reportingDate, $obj)
    {
        App::make(SubmissionCore::class)->checkCenterDate($center, $reportingDate);
        $userId = $this->context->getUser()->id;

        $conf = $this->combinedTypeMapping[get_class($obj)];
        $idAttr = $conf['idAttr'];
        $toArrayFunc = array_get($conf, 'toArray', 'toArray');

        $params = [
            'center_id' => $center->id,
            'reporting_date' => $reportingDate,
            'stored_type' => $conf['key'],
            'stored_id' => $obj->$idAttr,
        ];
        $current = Models\SubmissionData::firstOrNew($params);
        $objArray = $obj->$toArrayFunc();
        $current->data = $objArray;
        $current->userId = $userId;
        $current->save();

        $params['data'] = $objArray;
        $params['user_id'] = $userId;
        Models\SubmissionDataLog::create($params);
    }

    // Return a simple ID for generation purposes that is not likely to be used.
    public function generateId()
    {
        return 0 - time();
    }

    /**
     * Taken array input which may contain an ID, find the ID at property $key.
     * If the property is nonexistent or null, we will generate a pseudo negative ID for it.
     * @param  array  $arr [description]
     * @param  string $key [description]
     * @return int  The numberic storage ID found or generated.
     */
    public function numericStorageId(array &$arr, $key = 'id')
    {
        $id = array_get($arr, $key, null);
        if ($id === null) {
            $arr[$key] = $id = $this->generateId();
            $arr['_idGenerated'] = true;
        } else {
            $id = intval($id);
        }

        return $id;
    }
}
