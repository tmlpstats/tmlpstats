<?php
namespace TmlpStats\Validate;

class NullValidator extends ValidatorAbstract
{
    protected function validate($data)
    {
        return true;
    }
}
