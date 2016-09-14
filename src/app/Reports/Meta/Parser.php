<?php namespace TmlpStats\Reports\Meta;

use Symfony\Component\Yaml\Yaml;
use TmlpStats\Reports\Domain;

class Parser
{
    public static function parse()
    {
        $yaml = Yaml::parse(file_get_contents('config/reports.yml'));

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

        // Parse API methods
        $r->api = self::parseApi($yaml['api'], '');

        // Parse reportMeta
        $r->reportMeta = self::parseReportMeta($yaml['reportMeta']);

        return $r;
    }

    private static function parseApi($apiItems, $prefix)
    {
        $result = [];
        foreach ($apiItems as $name => $item) {
            $item['absName'] = $prefix . $name;
            if (array_get($item, 'type') == 'namespace') {
                $item['children'] = self::parseApi($item['children'], $item['absName'] . '.');
                $ns = new Domain\ApiNamespace($name, $item);
                $result[] = $ns;
            } else {
                $result[] = new Domain\ApiMethod($name, $item);
            }
        }

        return $result;
    }

    private static function parseReportMeta($reportMeta)
    {
        $result = [];
        foreach ($reportMeta as $id => $item) {
            $childrenFlat = [];
            $item['children'] = self::parseReportMetaItems($item['children'], $childrenFlat);
            $item['childrenFlat'] = $childrenFlat;
            $result[$id] = new Domain\ReportMetaNamespace($id, $item);
        }

        return $result;
    }

    private static function parseReportMetaItems($apiItems, &$flatItems)
    {
        $result = [];
        foreach ($apiItems as $id => $item) {
            if (array_get($item, 'children', null) !== null) {
                $item['children'] = self::parseReportMetaItems($item['children'], $flatItems);
            }
            $v = new Domain\ReportMetaItem($id, $item);
            $result[] = $v;
            $flatItems[] = $v;
        }

        return $result;
    }
}

class ParseResult
{
    public $reports = [];
    public $scopes = [];
    public $access_levels = [];
    public $access_level_aliases = [];
    public $api = [];
    public $reportMeta = [];

    public function scope($id)
    {
        if (!isset($this->scopes[$id])) {
            throw new \Exception("Scope '{$id}' not found");
        }

        return $this->scopes[$id];
    }

    public function expand_access_levels($levels)
    {
        $result = [];
        foreach ($levels as $key) {
            foreach ($this->access_level_aliases[$key] as $alias) {
                $result[] = $alias;
            }
        }

        return $result;
    }

    public function apiFlat()
    {
        $methods = [];
        $namespaces = [];

        $this->flattenedApi($this->api, $methods, $namespaces);

        return compact('methods', 'namespaces');

    }

    private function flattenedApi($api, &$methods, &$namespaces)
    {
        foreach ($api as $item) {
            if ($item instanceof Domain\ApiNamespace) {
                $namespaces[] = $item;
                $this->flattenedApi($item->children, $methods, $namespaces);
            } else {
                $methods[] = $item;
            }
        }
    }
}
