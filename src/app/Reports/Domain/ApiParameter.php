<?php
namespace TmlpStats\Reports\Domain;

/**
 * ApiParameter represents a single parameter for an API method
 */
class ApiParameter
{
    /**
     * The name of this parameter
     * @var string
     */
    public $name = '';
    public $type = '';
    public $required = true;
    /**
     * [$isLast description]
     * @var boolean
     */
    public $isLast = false;

    public function __construct($body)
    {
        $this->name = $body['name'];
        $this->type = $body['type'];

        if (isset($body['required'])) {
            $this->required = ($body['required'] == 'true');
        }
    }

    public function absNameLocal()
    {
        return str_replace('.', '__', $this->absName);
    }
}
