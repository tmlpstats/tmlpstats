<?php
namespace TmlpStats\Traits;

use App;
use Tmlpstats\Api;

trait UsesContext
{
    protected $_context = null;

    protected function context()
    {
        if ($this->_context == null) {
            $this->_context = App::make(Api\Context::class);
        }

        return $this->_context;
    }
}
