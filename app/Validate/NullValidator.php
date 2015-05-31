<?php
namespace TmlpStats\Validate;

class NullValidator extends ValidatorAbstract
{
    protected function populateValidators($data) { }
    protected function validate($data)
    {
        return true;
    }
}
