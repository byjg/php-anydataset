<?php

namespace ByJG\AnyDataset\Dataset;

use SparQL\Connection;

class SparQLDataset
{

    /**
     * @var object
     */
    private $connection;

    /**
     *
     * @param string $url
     * @param string $namespaces
     */
    public function __construct($url, $namespaces = null)
    {
        $this->connection = new Connection($url);

        if (is_array($namespaces)) {
            foreach ($namespaces as $key => $value) {
                $this->connection->ns($key, $value);
            }
        }

        if (function_exists('dba_open')) {
            $cache = sys_get_temp_dir() . "/caps.db";
            $this->connection->capabilityCache($cache);
        }
    }

    public function getCapabilities()
    {
        $return = array();

        if (function_exists('dba_open')) {
            foreach ($this->connection->capabilityCodes() as $code) {
                $return[$code] = array($this->connection->supports($code), $this->connection->capabilityDescription($code));
            }
        }

        return $return;
    }

    /**
     * @param string $sparql
     * @return DbIterator
     */
    public function getIterator($sparql)
    {
        $result = $this->connection->query($sparql);
        return new SparQLIterator($result);
    }
}
