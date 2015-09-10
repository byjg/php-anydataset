<?php

namespace ByJG\AnyDataset\Repository;

use SparQL\Connection;

class SparQLDataset
{

    /**
     * @var object
     */
    private $_db;

    /**
     *
     * @param string $json
     */
    public function __construct($url, $namespaces = null)
    {
        $this->_db = new Connection($url);

        if (is_array($namespaces)) {
            foreach ($namespaces as $key => $value) {
                $this->_db->ns($key, $value);
            }
        }

        if (function_exists('dba_open')) {
            $cache = sys_get_temp_dir() . "/caps.db";
            $this->_db->capabilityCache($cache);
        }
    }

    public function getCapabilities()
    {
        $return = array();

        if (function_exists('dba_open')) {
            foreach ($this->_db->capabilityCodes() as $code) {
                $return[$code] = array($this->_db->supports($code), $this->_db->capabilityDescription($code));
            }
        }

        return $return;
    }

    /**
     * @param string $sql
     * @param array $array
     * @return DBIterator
     */
    public function getIterator($sparql)
    {
        $result = $this->_db->query($sparql);
        return new SparQLIterator($result);
    }
}
