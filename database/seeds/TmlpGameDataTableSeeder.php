<?php

use TmlpStats\TmlpGameData;
use TmlpStats\Seeders\CsvSeederAbstract;

class TmlpGameDataTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "tmlp_games_data.csv";

    protected function createObject($data)
    {
        TmlpGameData::create($data);
    }
}

