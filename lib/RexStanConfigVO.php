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
     * @parameter $level int
     * @parameter $addons string
     * @parameter $extensions string
     * @parameter $phpVersion string
     */
    function __construct(int $level = 0, string $addons = '', string $extensions = '', string $phpVersion = '')
    {
        $this->level = $level;
        $this->addons = $addons;
        $this->extensions = $extensions;
        $this->phpVersion = $phpVersion;
    }

    /**
     * Get level
     * @api
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Get addons
     * @api
     * @return string
     */
    public function getAddons()
    {
        return $this->addons;
    }

    /**
     * Get extensions
     * @api
     * @return string
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Get phpVersion
     * @api
     * @return string
     */
    public function getPhpVersion()
    {
        return $this->phpVersion;
    }

}
