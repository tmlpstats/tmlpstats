<?php
namespace TmlpStats\Api;

use App;
use Carbon\Carbon;
use Illuminate\Auth\Guard;
use Illuminate\Http\Request;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Api\Exceptions as ApiExceptions;
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
    ];

    protected $keyTypeMapping = [];
    protected $combinedTypeMapping = [];

    // Make a new SubmissionData API. Since this class is a singleton in the App constructor,
    // we know that the two mappings will only get made once.
    public function __construct(Context $context, Guard $auth, Request $request)
    {
        parent::__construct($context, $auth, $request);

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
        $className = $conf['class'];

        $result = Models\SubmissionData::centerDate($center, $reportingDate)->type($conf['key'])->get();

        return $result->map(function ($row) use ($className) {
            return $className::fromArray($row->data);
        });
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
}
