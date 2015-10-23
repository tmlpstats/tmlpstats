<?php namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use TmlpStats\Reports\Meta\Parser;

class ReportsWiki extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'reports:wiki';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export reports in wiki syntax.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $bucket = Parser::parse();
        foreach ($bucket->reports as $report) {
            if ($report->ticket != null) {
                $report->ticket_link = "[#{$report->ticket}](https://github.com/pdarg/tmlpstats/issues/{$report->ticket})";
            } else {
                $report->ticket_link = "";
            }
        }
        print view('reports.tools.wiki_output', ['bucket' => $bucket ])->render();
    }
}
