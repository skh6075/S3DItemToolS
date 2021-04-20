<?php

namespace skh6075\s3ditemtools\skin;

use pocketmine\utils\SingletonTrait;
use skh6075\s3ditemtools\S3DItemToolS;
use function array_diff;
use function file_exists;

final class SkinFactory{
    use SingletonTrait;

    /** @var SkinReflection[] */
    private static array $skins = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function init(): void{
        foreach (array_diff(scandir(S3DItemToolS::getInstance()->getDataFolder() . "models/"), ['.', '..']) as $value) {
            if (pathinfo($value, PATHINFO_EXTENSION) !== "json")
                continue;

            $filename = pathinfo($value, PATHINFO_FILENAME);
            if (!file_exists(S3DItemToolS::getInstance()->getDataFolder() . "images/" . $filename . ".png"))
                continue;

            self::$skins[$filename] = new SkinReflection($filename);
        }
    }

    public function getSkin(string $name): ?SkinReflection{
        return self::$skins[$name] ?? null;
    }
}