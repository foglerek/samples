<?php namespace Anon\Insights\Fetchers;

class QuxFetcher extends DataFetcher
{
    protected static
        $cache = array(),
        $outputColumns = array(
            'qux_id'
        );

    public static function getOutput()
    {
        $idList = self::getIds();

        return array_map(function($thud) {
            return ['qux_id' => $thud];
        }, $idList);
    }

    public static function getChildCount()
    {
        $fooIds = FooFetcher::getIds();

        self::log('Fetching Qux Children Count.');

        return \DB::select(\DB::raw("
            SELECT
                COUNT(*) AS child_count
            FROM qux_children AS children
            INNER JOIN qux_parents AS parents
                ON parents.id = children.parent_id
            WHERE parents.foo_id IN (
                " . implode( ',', $fooIds ) . "
            )
            AND qux_children.is_relevant = 1
        "))[0]['child_count'];
    }

    public static function getIds()
    {
        $fooIds = FooFetcher::getIds();

        if (empty($fooIds)) {
            return array();
        }

        if (empty(self::$cache ) ) {
            self::log('Fetching Unique Quxs.');

            $data = \DB::select( \DB::raw("
                SELECT DISTINCT
                    qux_id as id
                FROM qux
                WHERE foo_id IN (
                    " . implode( ',', $fooIds ) . "
                )
                AND qux_id > 0
            "));

            self::$cache = array_pluck($data, 'id');
        }

        return self::$cache;
    }
}
