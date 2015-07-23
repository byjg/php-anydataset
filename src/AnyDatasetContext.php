<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace ByJG\AnyDataset;

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
     * @var \Iconfig\Config
     */
    private $config;

    protected function __construct()
    {
        $this->config = new \Iconfig\Config('config');
    }

    public function getDebug()
    {
        return $this->config->getAnydatasetconfig('debug');
    }
}
