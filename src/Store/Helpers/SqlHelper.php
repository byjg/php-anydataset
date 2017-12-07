<?php

namespace ByJG\AnyDataset\Store\Helpers;

class SqlHelper
{
    public static function createSafeSQL($sql, $list)
    {
        return str_replace(array_keys($list), array_values($list), $sql);
    }
}
