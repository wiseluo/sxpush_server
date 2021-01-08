<?php
namespace App\HttpController\Push\Platform;

use App\HttpController\Push\Platform\Config;

abstract class Gateway
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    /**
     * @return \MingYuanYun\Push\Support\Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param \MingYuanYun\Push\Support\Config $config
     *
     * @return $this
     */
    public function setConfig(Config $config)
    {
        $this->config = $config;

        return $this;
    }

}
