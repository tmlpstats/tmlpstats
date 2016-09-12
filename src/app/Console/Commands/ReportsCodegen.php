<?php
namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;
use TmlpStats\Reports\Meta\Parser;

class ReportsCodegen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:codegen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate various outputs from reports.yml';

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
    public function handle()
    {
        $bucket = Parser::parse();
        $flat = $bucket->apiFlat();
        $apiMethodsFlat = $flat['methods'];
        $namespacesFlat = $flat['namespaces'];
        $reportMeta = $bucket->reportMeta;
        $packageNames = $this->uniquePackageNames($apiMethodsFlat);

        // Write PHP
        $apiController = view('api.codegen.api_controller_output', compact('apiMethodsFlat'))->render();
        $this->writeFile('app/Http/Controllers/ApiController.php', $apiController);

        $apiProvider = view('api.codegen.api_provider_output', compact('packageNames'))->render();
        $this->writeFile('app/Providers/ApiProvider.php', $apiProvider);

        // Write a javascript API. PROTOTYPE
        $script = view('api.codegen.js_output', compact('namespacesFlat'))->render();
        $this->writeFile('public/js/api.js', $this->stripLine($script));

        $es6 = view('api.codegen.es6_output', compact('namespacesFlat'))->render();
        $this->writeFile('resources/assets/js/api/api-generated.js', $this->stripLine($es6));

        $script = view('api.codegen.reportmeta_es6_output', compact('reportMeta'))->render();
        $this->writeFile('resources/assets/js/classic/reports-generated.js', $this->stripLine($script));
        foreach ($reportMeta as $k => $namespace) {
            $source = view('api.codegen.reportmeta_dispatch_trait', compact('reportMeta', 'namespace'))->render();
            $this->writeFile("app/Http/Controllers/Traits/{$k}ReportDispatch.php", $source);
        }
    }

    private function writeFile($name, $output)
    {
        $fp = fopen($name, 'w');
        fputs($fp, $output);
        fclose($fp);
    }

    private function uniquePackageNames($apiMethodsFlat)
    {
        $p = '';
        $packages = [];
        foreach ($apiMethodsFlat as $api) {
            if (($name = $api->packageName()) != $p) {
                $p = $name;
                $packages[] = $name;
            }
        }

        return $packages;
    }

    // strip the first line off.
    private function stripLine($text)
    {
        $lines = explode("\n", $text);
        array_shift($lines);

        return implode("\n", $lines);
    }
}
