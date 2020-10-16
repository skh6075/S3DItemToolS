<?php

namespace skh6075\S3DItemToolS\factory\player;


use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\Server;
use skh6075\S3DItemToolS\factory\skin\SkinMap;
use skh6075\S3DItemToolS\S3DItemToolS;

class PlayerSkin{

    /** @var PlayerSkin */
    private static $instance = null;
    
    /** @var Skin[] */
    private static $skins = [];

    
    public static function getInstance (): PlayerSkin{
        if (self::$instance === null) {
            self::$instance = new self ();
        }
        return self::$instance;
    }
    
    private function __construct () {
    }

    /**
     * @param Player $player
     */
    public function setPlayerSkin(Player $player): void{
        self::$skins[$player->getName()] = $player->getSkin();
    }

    /**
     * @param Player $player
     */
    public function getPlayerSkin(Player $player): ?Skin{
        return self::$skins[$player->getName()] ?? null;
    }

    /**
     * @param Player $player
     */
    public function callbackSkin(Player $player): void{
        if (($skin = $this->getPlayerSkin($player)) instanceof Skin) {
            $this->broadcastChangeSkin($player, $skin);
        }
    }

    /**
     * @param string $skinData
     */
    private function convertSkinImage(string $skinData) {
        $size = strlen($skinData);
        $width = SkinMap::SKIN_WIDTH_SIZE[$size];
        $height = SkinMap::SKIN_HEIGHT_SIZE[$size];
        $pos = 0;
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        for ($y = 0; $y < $height; $y ++) {
            for ($x = 0; $x < $width; $x ++) {
                $r = ord($skinData[$pos]);
                $pos ++;
                $g = ord($skinData[$pos]);
                $pos ++;
                $b = ord($skinData[$pos]);
                $pos ++;
                $a = 127 - intdiv(ord($skinData[$pos]), 2);
                $pos ++;
                $color = imagecolorallocatealpha($image, $r, $g, $b, $a);
                imagesetpixel($image, $x, $y, $color);
            }
        }
        imagesavealpha($image, true);
        return $image;
    }

    /**
     * @param Player $player
     */
    public function makeSkinImage(Player $player): void{
        $skinPath = S3DItemToolS::getInstance()->getDataFolder() . "skins/" .$player->getName() . ".png";
        $image = $this->convertSkinImage($player->getSkin()->getSkinData());
        $background = imagecolorallocate($image, 255, 255, 255);
        imagecolortransparent($image, $background);
        imagepng($image, $skinPath);
        imagedestroy($image);
    }

    /**
     * @param Player $player
     */
    public function resetSkinImage(Player $player): void{
        if (file_exists(S3DItemToolS::getInstance()->getDataFolder() . "skins/" . $player->getName() . ".png")) {
            unlink(S3DItemToolS::getInstance()->getDataFolder() . "skins/" . $player->getName() . ".png");
        }
        if (file_exists(S3DItemToolS::getInstance()->getDataFolder() . "images/" . $player->getName() . ".png")) {
            unlink(S3DItemToolS::getInstance()->getDataFolder() . "images/" . $player->getName() . ".png");
        }
        if (isset (self::$skins[$player->getName()]))
            unset (self::$skins[$player->getName()]);
    }

    /**
     * @param Player $player
     * @param string $resource
     */
    public function convertImageMerge(Player $player, string $resource): void{
        $aimage = imagecreatefrompng(S3DItemToolS::getInstance()->getDataFolder() . "skins/" . $player->getName() . ".png");
        $bimage = imagecreatefrompng(S3DItemToolS::getInstance()->getDataFolder() . "images/" . $resource . ".png");
        [$width, $height] = getimagesize(S3DItemToolS::getInstance()->getDataFolder() . "skins/" . $player->getName() . ".png");
        imagecopymerge($aimage, $bimage, 56, 16, 0, 0, $width, $height, 100);
        imagesavealpha($aimage, true);
        imagepng($aimage, S3DItemToolS::getInstance()->getDataFolder() . "images/" . $player->getName() . ".png");
        imagedestroy($aimage);
        imagedestroy($bimage);
    }

    /**
     * @param Player $player
     * @param string $resource
     */
    public static function sendImageSkin(Player $player, string $resource): void{
        $path = S3DItemToolS::getInstance()->getDataFolder() . "images/" . $player->getName() . ".png";
        $image = imagecreatefrompng($path);
        $skinbytes = "";
        $size = (int) getimagesize($path) [1];
        for ($y = 0; $y < $size; $y ++) {
            for ($x = 0; $x < 64; $x ++) {
                $colorat = imagecolorat($image, $x, $y);
                $a = ((~((int)($colorat >> 24))) << 1) & 0xff;
                $r = ($colorat >> 16) & 0xff;
                $g = ($colorat >> 8) & 0xff;
                $b = $colorat & 0xff;
                $skinbytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        imagedestroy($image);
        $skin = new Skin($player->getSkin()->getSkinId(), $skinbytes, "", "geometry." . $resource, file_get_contents(S3DItemToolS::getInstance()->getDataFolder() . "models/" . $resource . ".json"));
        $this->broadcastChangeSkin($player, $skin);
    }

    /**
     * @param Player $player
     * @param Skin $skin
     */
    private function broadcastChangeSkin(Player $player, Skin $skin): void{
        $player->setSkin($skin);
        foreach(Server::getInstance()->getOnlinePlayers() as $players) {
            $player->sendSkin([$players, $player]);
        }
    }
}
