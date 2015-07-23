<?php

namespace ByJG\AnyDataset\Database;

/**
 * @package xmlnuke
 */

/**
 * Class to create and manipulate Several Data Types
 *
 */

class XmlnukeProviderFactory
{
	/**
	 * Each provider have your own model for pass parameter. This method define how each provider name define the parameters
	 *
	 * @param ConnectionManagement $connData
	 * @return string
	 */
	public static function GetParamModel($connData)
	{
		if ($connData->getExtraParam("parammodel") != "")
		{
			return $connData->getExtraParam("parammodel");
		}
		elseif ($connData->getDriver() == "sqlrelay")
		{
			return "?";
		}
		else
		{
			return ":_";
		}
	}

	/**
	 * Transform generic parameters [[PARAM]] in a parameter recognized by the provider name based on current DbParameter array.
	 *
	 * @param ConnectionManagement $connData
	 * @param string $SQL
	 * @param array $param
	 * @return array An array with the adjusted SQL and PARAMs
	 */
	public static function ParseSQL($connData, $SQL, $params = null)
	{
		if ($params == null)
			return $SQL;
		
		$paramSubstName = XmlnukeProviderFactory::GetParamModel ( $connData );
		foreach ( $params as $key => $value )
		{
			$arg = str_replace ( "_", XmlnukeProviderFactory::KeyAdj ( $key ), $paramSubstName );
			if (strpos($SQL, "[[" . $key . "]]") !== false)
				$SQL = str_replace ( "[[" . $key . "]]", $arg, $SQL );
			else
				unset($params[$key]);
		}

		$SQL = preg_replace("/\[\[(.*?)\]\]/", "null", $SQL);

		return array($SQL, $params);
	}

	public static function KeyAdj($key)
	{
		return str_replace ( ".", "_", $key );
	}

}


?>