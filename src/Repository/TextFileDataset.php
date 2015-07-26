<?php

namespace ByJG\AnyDataset\Repository;

use ByJG\AnyDataset\Exception\DatasetException;
use ByJG\AnyDataset\Exception\NotFoundException;
use Exception;
use InvalidArgumentException;

class TextFileDataset
{
	const CSVFILE = '/[|,;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
	const CSVFILE_SEMICOLON = '/[;](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';
	const CSVFILE_COMMA = '/[,](?=(?:[^"]*"[^"]*")*(?![^"]*"))/';

	protected $_source;

	protected $_fields;

	protected $_fieldexpression;

	protected $_sourceType;


	/**
	 * Text File Data Set
	 *
	 * @param string $source
	 * @param array $fields
	 * @param string $fieldexpression
	 * @return TextFileDataset
	 */
	public function __construct($source, $fields, $fieldexpression = null)
	{
		if (is_null($fieldexpression)) {
            $fieldexpression = TextFileDataset::CSVFILE;
        }

        if (!is_array($fields))
		{
			throw new InvalidArgumentException("You must define an array of fields.");
		}
		if (!preg_match('~(http|https|ftp)://~', $source))
		{
			$this->_source = $source;

			if (!file_exists($this->_source))
			{
				throw new NotFoundException("The specified file " . $this->_source . " does not exists")	;
			}

			$this->_sourceType = "FILE";
		}
		else
		{
			$this->_source = $source;
			$this->_sourceType = "HTTP";
		}


		$this->_fields = $fields;

		if ($fieldexpression == 'CSVFILE')
		{
			$this->_fieldexpression = TextFileDataset::CSVFILE;
		}
		else
		{
			$this->_fieldexpression = $fieldexpression;
		}
	}

	/**
	*@access public
	*@param string $sql
	*@param array $array
	*@return DBIterator
	*/
	public function getIterator()
	{
		$old = ini_set('auto_detect_line_endings', true);
		$handle = @fopen($this->_source, "r");
		ini_set('auto_detect_line_endings', $old);
		if (!$handle)
		{
			throw new DatasetException("TextFileDataset failed to open resource");
		}
		else
		{
			try
			{
				$it = new TextFileIterator($handle, $this->_fields, $this->_fieldexpression);
				return $it;
			}
			catch (Exception $ex)
			{
				fclose($handle);
			}
		}
	}

}

