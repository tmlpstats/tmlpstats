<?php

use TmlpStats\TmlpGame;
use TmlpStats\Seeders\CsvSeederAbstract;

class TmlpGameTableSeeder extends CsvSeederAbstract {

    protected $exportFile = "tmlp_games.csv";

    protected function createObject($data)
    {
        TmlpGame::create($data);
    }
}

