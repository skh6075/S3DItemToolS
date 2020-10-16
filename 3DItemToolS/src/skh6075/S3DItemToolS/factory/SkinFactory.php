<?php


namespace skh6075\S3DItemToolS\factory;

/**
 * @return string
 */
define ("TYPE_JSON", "json");

use skh6075\S3DItemToolS\factory\skin\SkinReflection;
use skh6075\S3DItemToolS\S3DItemToolS;

class SkinFactory{
    
    /** @var SkinReflection[] */
    private static $reflections = [];

    /** @var string[] */
    private const DIRECTORIES = [
        "models",
        "images",
        "skins"
    ];
    

    public function init(): void{
        self::createData();
        self::loadReflections();
    }

    private static function createData(): void{
        foreach (self::DIRECTORIES as $str) {
            if (!is_dir(S3DItemToolS::getInstance()->getDataFolder() . $str . DIRECTORY_SEPARATOR))
                @mkdir(S3DItemToolS::getInstance()->getDataFolder() . $str . DIRECTORY_SEPARATOR);
        }
    }

    private static function loadReflections(): void{
        foreach (array_diff(scandir(S3DItemToolS::getInstance()->getDataFolder() . "models" . DIRECTORY_SEPARATOR), [ '.', '..' ]) as $value) {
            if (!isset(explode('.', $value) [1]) || explode('.', $value) [1] !== TYPE_JSON) {
                continue;
            }
            self::$reflections[explode('.', $value) [0]] = new SkinReflection(explode('.', $value) [0]);
        }
    }

    /**
     * @param string $name
     * @return SkinReflection|null
     */
    public static function getSkinReflection(string $name): ?SkinReflection{
        return self::$reflections[$name] ?? null;
    }
}
