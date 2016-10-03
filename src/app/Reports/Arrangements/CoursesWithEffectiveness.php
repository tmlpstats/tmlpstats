<?php namespace TmlpStats\Reports\Arrangements;

class CoursesWithEffectiveness extends BaseArrangement
{
    /*
     * Builds an array of courses with effectiveness calculations
     */
    public function build($data)
    {
        $courses = $data['courses'];
        $reportingDate = $data['reportingDate'];
        $includeTotals = isset($data['includeTotals']) && $data['includeTotals'];
        $combineCompleted = isset($data['combineCompleted']) && $data['combineCompleted'];

        $copyFields = [
            'courseId',
            'quarterStartTer',
            'quarterStartStandardStarts',
            'quarterStartXfer',
            'currentTer',
            'currentStandardStarts',
            'currentXfer',
            'completedStandardStarts',
            'potentials',
            'registrations',
            'guestsPromised',
            'guestsInvited',
            'guestsConfirmed',
            'guestsAttended',
        ];

        $totals = [
            'CAP' => [
                'open' => array_fill_keys($copyFields, 0),
                'closed' => array_fill_keys($copyFields, 0),
                'all' => array_fill_keys($copyFields, 0),
            ],
            'CPC' => [
                'open' => array_fill_keys($copyFields, 0),
                'closed' => array_fill_keys($copyFields, 0),
                'all' => array_fill_keys($copyFields, 0),
            ],
        ];

        $reportData = [];
        foreach ($courses as $courseData) {

            $type = $courseData->course->type;

            $course = [
                'centerName' => $courseData->course->center->name,
                'location'   => $courseData->course->location ?: $courseData->course->center->name,
                'startDate'  => $courseData->course->startDate,
                'type'       => $type,
            ];

            $isComplete = $course['startDate']->lt($reportingDate);

            foreach ($copyFields as $field) {
                $course[$field] = $courseData->$field;

                if ($includeTotals) {
                    $state = $isComplete ? 'closed' : 'open';

                    $totals[$type][$state][$field] += $courseData->$field;
                    $totals[$type]['all'][$field] += $courseData->$field;
                }
            }

            $course['completionStats'] = $this->calculateEffectiveness($course, $isComplete);

            if ($isComplete && !$combineCompleted) {
                $type = 'completed';
            }

            $reportData[$type][] = $course;
        }

        if ($includeTotals) {
            foreach (['CAP', 'CPC'] as $type) {
                foreach (['open', 'closed', 'all'] as $state) {
                    $totals[$type][$state] = array_merge(
                        $totals[$type][$state],
                        $this->calculateEffectiveness($totals[$type][$state], $state !== 'open')
                    );
                }
            }

            $reportData['CAP']['totals'] = $totals['CAP'];
            $reportData['CPC']['totals'] = $totals['CPC'];
        }

        return compact('reportData');
    }

    protected function calculateEffectiveness($courseData, $isComplete)
    {
        $result = [
            'registrationFulfillment' => 0,
            'registrationEffectiveness' => 0,
            'guestsGameEffectiveness' => 0,
        ];

        $result['registrationFulfillment'] = $this->getPercent(
            $courseData['currentStandardStarts'],
            $courseData['currentTer']
        );

        if ($isComplete) {
            $result['registrationEffectiveness'] = $this->getPercent(
                $courseData['registrations'],
                $courseData['potentials']
            );
            if ($courseData['guestsPromised']) {
                $result['guestsGameEffectiveness'] = $this->getPercent(
                    $courseData['guestsAttended'],
                    $courseData['guestsPromised']
                );
            }
        }

        return $result;
    }

    protected function getPercent($actual, $promise)
    {
        if (!$promise) {
            return 0;
        }

        return round(($actual / $promise) * 100);
    }
}
