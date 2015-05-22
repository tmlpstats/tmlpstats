<?php
namespace TmlpStats\Validate;

class NullValidator extends ValidatorAbstract
{
    protected function populateValidators($data) { }
    protected function validate($date)
    {
        return true;
    }
}
