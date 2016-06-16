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
     * @param  array  $data  Array of objects to validate
     * @return boolen
     */
    public function run($data)
    {
        $isValid = true;

        foreach ($data as $type => $list) {
            $apiType = 'api' . ucfirst($type);
            if (!$this->processDataList($apiType, $list)) {
                $isValid = false;
            }
        }

        $validator = ValidatorFactory::build($this->statsReport, 'apiCenterGames');
        if (!$validator->run($data)) {
            $isValid = false;
        }
        $this->mergeMessages($validator->getMessages());

        return $isValid;
    }

    public function runOne($data, $id = null)
    {
        $isValid = true;

        $type = class_basename(get_class($data));
        $apiType = 'api' . ucfirst($type);

        $validator = ValidatorFactory::build($this->statsReport, $apiType);
        $validator->setOffset($id);
        if (!$validator->run($data)) {
            $isValid = false;
        }
        $this->mergeMessages($validator->getMessages());

        return $isValid;
    }

    protected function processDataList($type, $list)
    {
        $isValid = true;
        foreach ($list as $id => $dataObj) {
            $validator = ValidatorFactory::build($this->statsReport, $type);
            $validator->setOffset($id);
            if (!$validator->run($dataObj)) {
                $isValid = false;
            }
            // TODO pair messages with object so we can match them up later when displaying to user
            $this->mergeMessages($validator->getMessages());
        }

        return $isValid;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function mergeMessages($messages)
    {
        $this->messages = array_merge($this->messages, $messages);
    }
}
