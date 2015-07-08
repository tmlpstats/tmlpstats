<?php

use TmlpStats\TmlpGame;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class TmlpGameTableSeeder extends TmlpStats\Seeders\CsvSeederAbstract {

    protected $exportFile = "tmlp_games.csv";

    protected function createObject($data)
    {
        TmlpGame::create($data);
    }
}

