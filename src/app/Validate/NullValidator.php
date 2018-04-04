<?php
namespace TmlpStats\Validate;

class NullValidator extends ApiValidatorAbstract
{
    protected function validate($data)
    {
        return true;
    }
}
