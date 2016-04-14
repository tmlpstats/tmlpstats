<?php
use Carbon\Carbon;
use Illuminate\Database\Seeder;

abstract class ArraySeeder extends Seeder
{
    protected $table = '';
    protected $columns = [];
    protected $insertData = [];

    abstract protected function initData();

    public function run()
    {
        $this->initData();

        $inserts = [];
        foreach ($this->insertData as $data) {
            $row = [];
            foreach ($this->columns as $i => $col) {
                $row[$col] = $data[$i];
            }
            $row['created_at'] = Carbon::now()->toDateTimeString();//DB::raw('NOW()');
            $row['updated_at'] = Carbon::now()->toDateTimeString();//DB::raw('NOW()');
            $inserts[] = $row;
        }

        DB::table($this->table)->insert($inserts);
    }
}



