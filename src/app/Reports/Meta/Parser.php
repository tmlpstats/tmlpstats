<?php namespace TmlpStats\Reports\Meta;

use Symfony\Component\Yaml\Yaml;
use TmlpStats\Reports\Domain;

class Parser {
    public static function parse() {
        $yaml = Yaml::parse(file_get_contents("config/reports.yml"));

        $r = new ParseResult();

        // Parse access levels
        foreach ($yaml['access_levels']['primary'] as $access_level) {
            $r->access_levels[$access_level] = true;
            $r->access_level_aliases[$access_level] = [$access_level];
        }

        foreach ($yaml['access_levels']['aliases'] as $alias => $target) {
            $r->access_level_aliases[$alias] = $target;
        }

        // Parse Scopes
        foreach ($yaml['scopes'] as $id => $body) {
            $r->scopes[$id] = new Domain\Scope($id, $body);
        }

        // Parse reports
        foreach ($yaml['reports'] as $id => $body) {
            $body['scope'] = $r->scope($body['scope']);
            $body['access'] = $r->expand_access_levels($body['access']);
            $r->reports[] = new Domain\Report($id, $body);
        }

        return $r;
    }
}

class ParseResult {
    public $reports = [];
    public $scopes = [];
    public $access_levels = [];
    public $access_level_aliases = [];

    public function scope($id) {
        if (!array_key_exists($id, $this->scopes)) {
            throw new \Exception("Scope '{$id}' not found");
        }
        return $this->scopes[$id];
    }

    public function expand_access_levels($levels) {
        $result = [];
        foreach ($levels as $key) {
            foreach ($this->access_level_aliases[$key] as $alias) {
                $result[] = $alias;
            }
        }
        return $result;
    }
}
