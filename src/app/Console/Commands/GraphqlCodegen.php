<?php
namespace TmlpStats\Console\Commands;

use Illuminate\Console\Command;

class GraphqlCodegen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'graphql:codegen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate various outputs from graphql';

    const MAGIC_COMMENT_OPEN = '// GRAPHQL_GENERATED';
    const MAGIC_COMMENT_CLOSE = '// END_GRAPHQL_GENERATED';

    public $scalarMap = [
        'Date' => 'GraphQL::type(\'Date\')',
        'String' => 'Type::string()',
        'Int' => 'Type::int()',
        'ID' => 'Type::id()',
        'Boolean' => 'Type::boolean()',
    ];

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
        exec('npm run gql');
        $input = file_get_contents('storage/app/graphql.json');
        $decoded = json_decode($input, true);
        $schema = $decoded['data']['__schema'];
        foreach ($schema['types'] as $typeDef) {
            if ($typeDef['kind'] !== 'OBJECT' || $typeDef['name'] == 'Query') {
                continue;
            } else if (starts_with($typeDef['name'], '__')) {
                $this->line("Skipping fake type {$typeDef['name']}");
                continue;
            }
            $this->line("Going to generate for {$typeDef['name']}");
            foreach ($typeDef['fields'] as &$field) {

                $f = $this->resolveType($field['type']);
                $this->line("  -> {$field['name']} $f");
                $field['__resolved'] = $f;
            }
            $this->writeType($typeDef);
        }
    }

    public function resolveType($type)
    {
        switch ($type['kind']) {
            case 'OBJECT':
                return "GraphQL::type('{$type['name']}')";
            case 'NON_NULL':
                return 'Type::nonNull(' . $this->resolveType($type['ofType']) . ')';
            case 'SCALAR':
                return $this->scalarMap[$type['name']];
            case 'LIST':
                return 'Type::listOf(' . $this->resolveType($type['ofType']) . ')';
            default:
                $this->line('unmapped' . print_r($type, true));

                return 'UNMAPPED';
        }
    }

    public function writeFile($name, $output)
    {
        $fp = fopen($name, 'w');
        fputs($fp, $output);
        fclose($fp);
    }

    public function writeType($typeDef)
    {
        $fName = "app/GraphQL/Type/{$typeDef['name']}Type.php";
        if (file_exists($fName)) {
            $lines = [];
            $endLines = [];

            $all = explode("\n", file_get_contents($fName));
            $in_block = false;
            $after_block = false;
            $alreadyResolved = [];
            foreach ($all as $line) {
                if ($after_block) {
                    $endLines[] = $line;
                    $matches = [];
                    if (preg_match('/function (resolve.*Field)/', $line, $matches)) {
                        $alreadyResolved[$matches[1]] = true;
                    }
                } else if ($in_block) {
                    if (str_contains($line, self::MAGIC_COMMENT_CLOSE)) {
                        $after_block = true;
                    }
                } else {
                    if (str_contains($line, self::MAGIC_COMMENT_OPEN)) {
                        $in_block = true;
                    } else {
                        $lines[] = $line;
                    }
                }
            }
            if (!$after_block) {
                throw new \Exception("Did not find block in $fName");
            }
        } else {
            $lines = $this->typeOpen($typeDef);
            $endLines = null;
        }

        $lines[] = '    ' . static::MAGIC_COMMENT_OPEN;
        $lines[] = '    protected $attributes = [';
        $lines[] = "        'name' => '{$typeDef['name']}',";
        $lines[] = "        'description' => \"" . addslashes($typeDef['description']) . '",';
        $lines[] = '    ];';
        $lines[] = '';
        $lines[] = '    public function fields()';
        $lines[] = '    {';
        $lines[] = '        return [';

        $resolveable = [];
        foreach ($typeDef['fields'] as $field) {
            $lines[] = "            '{$field['name']}' => [";
            $lines[] = "                'type' => {$field['__resolved']},";
            if ($field['description']) {
                $lines[] = "                'description' => \"" . addslashes($field['description']) . '",';
            }
            $args = array_get($field, 'args', null);
            if ($args && count($args)) {
                $targetResolver = 'resolve' . ucfirst($field['name']) . 'Field';
                if (!($alreadyResolved[$targetResolver] ?? false)) {
                    $resolveable[] = $targetResolver;
                }

                $lines[] = "                'args' => [";
                foreach ($args as $arg) {
                    $aType = $this->resolveType($arg['type']);
                    $lines[] = "                    '{$arg['name']}' => [";
                    $lines[] = "                        'type' => {$aType},";
                    if ($default = array_get($arg, 'defaultValue', null)) {
                        $lines[] = "                    'defaultValue' => {$default},";
                    }
                    $lines[] = '                    ],';

                }
                $lines[] = '                ],';
            }
            $lines[] = '            ],';
        }

        $lines[] = '        ];';
        $lines[] = '    }';
        $lines[] = '    ' . static::MAGIC_COMMENT_CLOSE;

        foreach ($resolveable as $x) {
            $lines[] = '';
            $lines[] = '    // You need to fill out the following function to handle resolving.';
            $lines[] = '    // Remove these comments and replace with normal doc comments at that time.';
            $lines[] = "    public function {$targetResolver}(\$root, \$args) {}";
        }

        if (!$endLines || !count($endLines)) {
            $endLines = $this->typeClose($typeDef);
        }

        $f = implode("\n", array_merge($lines, $endLines));
        $this->writeFile($fName, $f);
    }

    public function typeOpen($typeDef)
    {
        return [
            '<?php',
            'namespace TmlpStats\GraphQL\Type;',
            '',
            'use Folklore\GraphQL\Support\Type as BaseType;',
            'use GraphQL;',
            'use GraphQL\Type\Definition\Type;',
            '',
            "class {$typeDef['name']}Type extends BaseType",
            '{',
            '    // the command "php artisan graphql:codegen" manages',
            '    // the definitions between the special graphql comments following',
            '',
        ];
    }

    public function typeClose($typeDef)
    {
        return [
            '',
            '}',
            '',
        ];
    }

}
