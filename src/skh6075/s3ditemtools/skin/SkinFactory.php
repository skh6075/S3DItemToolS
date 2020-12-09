<?php

namespace skh6075\s3ditemtools\skin;

use skh6075\s3ditemtools\S3DItemToolS;
use function array_diff;
use function explode;
use function file_exists;
use const DIRECTORY_SEPARATOR;

final class SkinFactory{

    /** @var ?SkinFactory */
    private static $instance = null;
    /** @var S3DItemToolS */
    protected $plugin;
    /** @var SkinReflection[] */
    private static $reflections = [];


    public static function getInstance(): ?SkinFactory{
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->plugin = S3DItemToolS::getInstance();
    }

    public function init(): void{
        foreach (array_diff(scandir($this->plugin->getDataFolder() . "models" . DIRECTORY_SEPARATOR), ['.', '..']) as $value) {
            if (!isset(explode(".", $value)[0]) || explode(".", $value)[1] !== "json" || !file_exists($this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $value . ".png")) {
                continue;
            }
            self::$reflections[explode(".", $value) [0]] = new SkinReflection(explode(".", $value) [1]);
        }
    }

    /**
     * @param string $name
     *
     * @return ?SkinReflection
     */
    public function getSkinReflection(string $name): ?SkinReflection{
        return self::$reflections[$name] ?? null;
    }
}