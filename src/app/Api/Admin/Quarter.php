<?php namespace TmlpStats\Api\Admin;

use Carbon\Carbon;
use TmlpStats as Models;
use TmlpStats\Api\Base\AuthenticatedApiBase;
use TmlpStats\Encapsulations;

class Quarter extends AuthenticatedApiBase {
    public function filter() {
        return Models\Quarter::all();
    }
}
