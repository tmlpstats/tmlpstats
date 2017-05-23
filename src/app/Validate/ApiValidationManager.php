<?php
namespace TmlpStats\Validate;

class ApiValidationManager
{
    protected $statsReport = null;
    protected $messages = [];

    public function __construct($statsReport)
    {
        $this->statsReport = $statsReport;
    }

    /**
     * Run the full suite of validators on $data
     *
     * Object should be grouped by object type. e.g.
     * $data = [
     *     'teamApplication' => [...Array of team application domain objects],
     *     'teamMember'      => [...Array of team member domain objects],
     *     'scoreboard'      => [...Array of scoreboard objects],
     *     'course'          => [...Array of course domain objects],
     * ];
     *
     * @param  array  $data       Array of objects to validate
     * @param  array  $pastWeeks  Array of objects from past reports
     * @return boolen
     */
    public function run($data, array $pastWeeks = [])
    {
        $isValid = true;

        foreach ($data as $type => $list) {
            if (!$this->processDataList($type, $list, $pastWeeks[$type])) {
                $isValid = false;
            }

            if (($type == 'TeamApplication' || $type == 'Course')
                && !$this->processDataList($type, $list, $pastWeeks[$type], "{$type}Change")
            ) {
                $isValid = false;
            }
        }

        $validator = ApiValidatorFactory::build($this->statsReport, 'CenterGames');
        if (!$validator->run($data, $pastWeeks)) {
            $isValid = false;
        }
        $this->mergeMessages($validator->getMessages(), 'Scoreboard');

        $validator = ApiValidatorFactory::build($this->statsReport, 'Accountability');
        if (!$validator->run($data, $pastWeeks)) {
            $isValid = false;
        }
        $this->mergeMessages($validator->getMessages(), 'TeamMember');

        return $isValid;
    }

    public function runOne($data, $id = null, array $pastWeeks = [])
    {
        $isValid = true;

        $type = class_basename(get_class($data));

        $validator = ApiValidatorFactory::build($this->statsReport, $type);
        if (!$validator->run($data, $pastWeeks)) {
            $isValid = false;
        }
        $this->mergeMessages($validator->getMessages());

        return $isValid;
    }

    protected function processDataList($type, $list, array $pastWeeks = [], $validatorClass = null)
    {
        $isValid = true;

        if (!$validatorClass) {
            $validatorClass = $type;
        }

        foreach ($list as $id => $dataObj) {
            $validator = ApiValidatorFactory::build($this->statsReport, $validatorClass);
            $lastWeek = [];
            if (isset($pastWeeks[$id])) {
                // we currently only pull the last weeks data, so wrap it in an array for now
                $lastWeek[] = $pastWeeks[$id];
            }

            if (!$validator->run($dataObj, $lastWeek)) {
                $isValid = false;
            }
            $this->mergeMessages($validator->getMessages(), $type);
        }

        return $isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function mergeMessages($messages, $type = null)
    {
        if (!$type) {
            $this->messages = array_merge($this->messages, $messages);
            return;
        }

        if (!isset($this->messages[$type])) {
            $this->messages[$type] = [];
        }

        $this->messages[$type] = array_merge($this->messages[$type], $messages);
    }
}
