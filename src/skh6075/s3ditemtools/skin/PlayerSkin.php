<?php

namespace skh6075\s3ditemtools\skin;

use pocketmine\entity\Skin;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use skh6075\s3ditemtools\S3DItemToolS;
use skh6075\s3ditemtools\utils\ImageUtils;
use function file_exists;
use function file_get_contents;
use function unlink;
use function chr;
use function imagesavealpha;
use function imagecreatefrompng;
use function imagecopymerge;
use function imagedestroy;
use function getimagesize;

final class PlayerSkin{
    use SingletonTrait;

    /** @var Skin[] */
    private static array $skins = [];

    public function __construct() {
        self::setInstance($this);
    }

    public function getPlayerSkin(Player $player): ?Skin{
        return self::$skins[spl_object_hash($player)] ?? null;
    }

    public function setPlayerSkin(Player $player): void{
        self::$skins[spl_object_hash($player)] = $player->getSkin();
    }

    public function makeSkinImage(Player $player): void{
        $skinPath = S3DItemToolS::getInstance()->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getName() . ".png";
        $image    = ImageUtils::skinDataToImageResource($player->getSkin()->getSkinData());
        $background = imagecolorallocate($image, 255, 255, 255);
        imagecolortransparent($image, $background);
        imagepng($image, $skinPath);
        imagedestroy($image);
    }

    public function convertImagemerge(Player $player, string $resource): void{
        $playerImage = imagecreatefrompng(S3DItemToolS::getInstance()->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getName() . ".png");
        $modelImage  = imagecreatefrompng(S3DItemToolS::getInstance()->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $resource . ".png");
        [$width, $height] = getimagesize(S3DItemToolS::getInstance()->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getName() . ".png");
        imagecopymerge($playerImage, $modelImage, 56, 16, 0, 0, $width, $height, 100);
        imagesavealpha($playerImage, true);
        imagepng($playerImage, S3DItemToolS::getInstance()->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getName() . ".png");
        imagedestroy($playerImage);
        imagedestroy($modelImage);
    }

    public function sendImageSkin(Player $player, string $resource): void{
        $path = S3DItemToolS::getInstance()->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getName() . ".png";
        $image = imagecreatefrompng($path);
        $skinBytes = "";
        $size = (int) getimagesize($path) [1];

        for ($y = 0; $y < $size; $y ++) {
            for ($x = 0; $x < 64; $x ++) {
                $colorat = imagecolorat($image, $x, $y);
                $a = ((~((int) ($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinBytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        imagedestroy($image);
        $skin = new Skin($player->getSkin()->getSkinId(), $skinBytes, "", "geometry." . $resource, file_get_contents(S3DItemToolS::getInstance()->getDataFolder() . "models" . DIRECTORY_SEPARATOR . $resource . ".json"));
        $this->broadcastChangeSkin($player, $skin);
    }

    public function broadcastChangeSkin(Player $player, Skin $skin): void{
        $player->setSkin($skin);
        $player->sendSkin(Server::getInstance()->getOnlinePlayers());
    }

    public function deleteSkinImage(Player $player): void{
        $plugin = S3DItemToolS::getInstance();
        if (file_exists($plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getName() . ".png")) {
            unlink($plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getName() . ".png");
        }

        if (file_exists($plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getName() . ".png")) {
            unlink($plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getName() . ".png");
        }

        if (isset(self::$skins[spl_object_hash($player)])) {
            unset(self::$skins[spl_object_hash($player)]);
        }
    }

    public function callbackSkin(Player $player): void{
        if (($skin = $this->getPlayerSkin($player)) instanceof Skin) {
            $this->broadcastChangeSkin($player, $skin);
        }
    }
}