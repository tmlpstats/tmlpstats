<?php
namespace TmlpStats\Validate;

class NullValidator extends ValidatorAbstract
{
    protected $classDisplayName = 'unspecified';

    protected function populateValidators($data) { }
    protected function validate($date)
    {
        return true;
    }
}
