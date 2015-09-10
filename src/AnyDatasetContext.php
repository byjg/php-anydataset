<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ByJG\AnyDataset;

use ByJG\AnyDataset\Exception\DatasetException;
use Iconfig\Config;

/**
 * Description of CacheContext
 *
 * @author jg
 */
class AnyDatasetContext
{

    use \ByJG\DesignPattern\Singleton;

    /**
     *
     * @var Config
     */
    private $config;

    protected function __construct()
    {
        $this->config = new Config('config');
    }

    public function getDebug()
    {
        return $this->config->getAnydatasetconfig('debug');
    }

    public function getConnectionString($name)
    {
        $config = $this->config->getAnydatasetconfig("connections.$name");
        if (empty($config) || !isset($config['url'])) {
            throw new DatasetException("Invalid configurarion 'connections.$name'");
        }
        return $config;
    }
}
