<?php namespace Anon\Insights\Fetchers;

use \Config;

class FooFetcher extends DataFetcher
{
    protected static
        $cache = array(),
        $outputColumns = array(
            'id',
            'name',
            'deadline_at'
        );

    public static function getOutput()
    {
        $idList = self::getIds();

        self::log('Fetching Unique Valid Foo Data.');
        return \DB::table('foo')
            ->select(self::$outputColumns)
            ->whereIn('id', $idList)
            ->get();
    }

    public static function getIds()
    {
        if (empty(self::$cache)) {

            $dateFrom = Config::get('Insights.dateFrom');
            $dateTo   = Config::get('Insights.dateTo');

            $dateFromConditional = ($dateFrom ? "AND foo.created_at >= '".$dateFrom."'" : "");
            $dateToConditional   = ($dateTo   ? "AND foo.created_at <= '".$dateTo."'"   : "");

            self::log('Fetching Unique Valid Foo Ids.');
            $data = \DB::select(\DB::raw("
                SELECT
                    validated_foos.id AS id
                FROM (
                    SELECT
                        foo.id AS id,
                        COUNT(DISTINCT baz.id) AS baz_count
                    FROM foo
                    INNER JOIN baz
                        ON baz.foo_id = foo.id
                    WHERE is_hidden = 0
                    ".$dateFromConditional."
                    ".$dateToConditional."
                    AND baz.submitted_at IS NOT NULL
                    GROUP BY foo.id
                    HAVING baz_count >= 10

                    UNION

                    SELECT
                        foo.id AS id,
                        COUNT(DISTINCT baz.id) AS baz_count
                    FROM foo
                    INNER JOIN baz
                        ON baz.foo_id = foo.id
                    WHERE is_hidden = 1
                    ".$dateFromConditional."
                    ".$dateToConditional."
                    AND baz.submitted_at IS NOT NULL
                    GROUP BY foo.id
                    HAVING baz_count >= 50
                ) as validated_foos
            "));

            self::$cache = array_pluck($data, 'id');
        }

        return self::$cache;
    }
}
