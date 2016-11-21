<?php namespace Anon\Insights\Fetchers;

use \Config;

class BarFetcher extends DataFetcher
{
    protected static
        $cache = array(),
        $outputColumns = array(
            'id',
            'name'
        );

    public static function getOutput()
    {
        $idList = self::getIds();

        self::log('Fetching Unique Valid Bar Data.');
        return \DB::table('bar')
            ->select(self::$outputColumns)
            ->whereIn('id', $idList)
            ->get();
    }

    public static function getIds()
    {
        if ( empty( self::$cache ) ) {

            $fooIds = FooFetcher::getIds();

            self::log('Fetching Unique Valid Bar Ids.');
            $data = \DB::select(\DB::raw("
                SELECT DISTINCT bar_id
                FROM foo
                WHERE id IN (".implode(',', $fooIds).")
            "));

            self::$cache = array_pluck($data, 'bar_id');
        }

        return self::$cache;
    }
}
