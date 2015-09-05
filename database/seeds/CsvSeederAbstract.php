<?php
namespace TmlpStats\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

abstract class CsvSeederAbstract extends Seeder {

    protected $exportPath = '../../export';
    protected $exportFile = '';

    abstract protected function createObject($data);

    public function run()
    {
        Model::unguard();

        $filename = __DIR__ . '/' . $this->exportPath . '/' . $this->exportFile;

        $csv = array();

        if (file_exists($filename)) {
            $csv = array_map('str_getcsv', file($filename));
        }

        if (count($csv) <= 1) {
            echo "No entries to import\n";
            return;
        }

        $droppedCount = 0;
        $fields = $csv[0];

        for ($i = 1; $i < count($csv); $i++) {

            $data = array();
            foreach ($csv[$i] as $k => $value) {

                if ($value === "NULL") {
                    $data[$fields[$k]] = null;
                } else {
                    $data[$fields[$k]] = $value;
                }
            }

            try {
                $this->createObject($data);
            } catch (\Exception $e) {
                // Ignore bad data
                $droppedCount++;
            }
        }
        if ($droppedCount) {
            echo "Dropped $droppedCount of $i rows from {$this->exportFile}\n";
        }
    }
}
