<?php

class RexStanConfigVO
{
    /** @var int */
    private $level = 0;

    /** @var string */
    private $addons = '';

    /** @var string */
    private $extensions = '';

    /** @var string */
    private $phpVersion = '';

    /**
     * Construtor
     * @param $level int
     * @param $addons string
     * @param $extensions string
     * @param $phpVersion string
     */
    function __construct(int $level, string $addons, string $extensions, string $phpVersion)
    {
        $this->level = $level;
        $this->addons = $addons;
        $this->extensions = $extensions;
        $this->phpVersion = $phpVersion;
    }

    public function getLevel():int
    {
        return $this->level;
    }

    public function getAddons():string
    {
        return $this->addons;
    }

    public function getExtensions():string
    {
        return $this->extensions;
    }

    public function getPhpVersion():string
    {
        return $this->phpVersion;
    }

}
