<?php

namespace skh6075\s3ditemtools;

use pocketmine\plugin\PluginBase;
use skh6075\s3ditemtools\skin\SkinFactory;
use function is_dir;
use function mkdir;
use const DIRECTORY_SEPARATOR;

class S3DItemToolS extends PluginBase{

    /** @var ?S3DItemToolS */
    private static $instance = null;
    /** @var string[] */
    protected $directories = [
        "models",
        "images",
        "skins"
    ];


    public static function getInstance(): ?S3DItemToolS{
        return self::$instance;
    }

    public function onLoad(): void{
        if (self::$instance === null) {
            self::$instance = $this;
        }
    }

    public function onEnable(): void{
        foreach ($this->directories as $directory) {
            if (is_dir($this->getDataFolder() . $directory . DIRECTORY_SEPARATOR)) {
                continue;
            }
            mkdir($this->getDataFolder() . $directory . DIRECTORY_SEPARATOR);
        }
        SkinFactory::getInstance()->init();
    }
}