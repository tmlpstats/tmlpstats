<?php
namespace TmlpStats;

use Respect\Validation\Validator as v;

class Message
{
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    protected $objectClassDisplayName = '';
    public static function create($className)
    {
        $me = new static();
        $me->objectClassDisplayName = $className;
        return $me;
    }
    public function reportWarning($message, $offset = false)
    {
        return $this->addMessage($message, 'warn', $offset);
    }
    public function reportError($message, $offset = false)
    {
        return $this->addMessage($message, 'error', $offset);
    }

    public function addMessage($message, $type, $offset = false)
    {
        if ($offset !== false)
        {
            $offsetType = $this->getOffsetType($offset);
            $offset = "$offsetType $offset";
        }
        return array(
            'type'    => $type,
            'section' => $this->objectClassDisplayName,
            'message' => $message,
            'offset'  => $offset,
        );
    }

    protected function getOffsetType($offset)
    {
        if (preg_match('/^[a-z]+$/i', $offset))
        {
            return 'column';
        }
        else if (preg_match('/^[\d]+$/', $offset))
        {
            return 'row';
        }
        else if (preg_match('/^[a-z]+[\d]+$/i', $offset))
        {
            return 'cell';
        }
        else
        {
            return 'offset';
        }
    }
}
