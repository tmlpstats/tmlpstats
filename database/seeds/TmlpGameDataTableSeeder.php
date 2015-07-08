<?php

use TmlpStats\TmlpGameData;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TmlpGameDataTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "tmlp_games_data.csv";

    protected function createObject($data)
    {
        TmlpGameData::create($data);
    }
}

