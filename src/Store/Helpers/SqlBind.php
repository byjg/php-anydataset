<?php

namespace ByJG\AnyDataset\Helpers;

use ByJG\Util\Uri;

/**
 * Class to create and manipulate Several Data Types
 *
 */
class SqlBind
{

    /**
     * Each provider have your own model for pass parameter.
     * This method define how each provider name define the parameters
     *
     * @param Uri $connData
     * @return string
     */
    public static function getParamModel(Uri $connData)
    {
        if ($connData->getQueryPart("parammodel") != "") {
            return $connData->getQueryPart("parammodel");
        } elseif ($connData->getScheme() == "sqlrelay") {
            return "?";
        } else {
            return ":_";
        }
    }

    /**
     * Transform generic parameters [[PARAM]] in a parameter recognized by the provider
     * name based on current DbParameter array.
     *
     * @param Uri $connData
     * @param string $sql
     * @param array $params
     * @return array An array with the adjusted SQL and PARAMs
     */
    public static function parseSQL(Uri $connData, $sql, $params = null)
    {
        if (is_null($params)) {
            return [$sql, null];
        }

        $paramSubstName = SqlBind::getParamModel($connData);
        foreach ($params as $key => $value) {
            $arg = str_replace("_", SqlBind::keyAdj($key), $paramSubstName);

            $count = 0;
            $sql = preg_replace("/(\[\[$key\]\]|:" . $key . "[\s\W]|:$key\$)/", $arg . ' ', $sql, -1, $count);
            if ($count === 0) {
                unset($params[$key]);
            }
        }

        $sql = preg_replace("/\[\[(.*?)\]\]/", "null", $sql);

        return [$sql, $params];
    }

    public static function keyAdj($key)
    {
        return str_replace(".", "_", $key);
    }
}
