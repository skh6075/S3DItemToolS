<?php

namespace skh6075\s3ditemtools\skin;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\Server;
use skh6075\s3ditemtools\S3DItemToolS;
use function file_exists;
use function file_get_contents;
use function unlink;
use function intdiv;
use function ord;
use function chr;
use function imagecreatetruecolor;
use function imagefill;
use function imagecolorallocatealpha;
use function imagesetpixel;
use function imagesavealpha;
use function imagecreatefrompng;
use function imagecopymerge;
use function imagedestroy;
use function getimagesize;

final class PlayerSkin{

    /** @var ?PlayerSkin */
    private static $instance = null;
    /** @var S3DItemToolS */
    protected $plugin;
    /** @var Skin[] */
    private static $skins = [];


    public static function getInstance(): ?PlayerSkin{
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->plugin = S3DItemToolS::getInstance();
    }

    public function getPlayerSkin(Player $player): ?Skin{
        return self::$skins[$player->getLowerCaseName()] ?? null;
    }

    public function setPlayerSkin(Player $player): void{
        self::$skins[$player->getLowerCaseName()] = $player->getSkin();
    }

    /**
     * @param Player $player
     */
    public function makeSkinImage(Player $player): void{
        $skinPath = $this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png";
        $image    = $this->convertSkinImage($player->getSkin()->getSkinData());
        $background = imagecolorallocate($image, 255, 255, 255);
        imagecolortransparent($image, $background);
        imagepng($image, $skinPath);
        imagedestroy($image);
    }

    /**
     * @param string $skinData
     * @return false|resource
     */
    private function convertSkinImage(string $skinData) {
        $size   = strlen($skinData);
        $width  = SkinMap::SKIN_WIDTH_SIZE[$size];
        $height = SkinMap::SKIN_HEIGHT_SIZE[$size];
        $pos    = 0;
        $image  = imagecreatetruecolor($width, $height);

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
     * @param string $resource
     */
    public function convertImagemerge(Player $player, string $resource): void{
        $playerImage = imagecreatefrompng($this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png");
        $modelImage  = imagecreatefrompng($this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $resource . ".png");
        [$width, $height] = getimagesize($this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png");
        imagecopymerge($playerImage, $modelImage, 56, 16, 0, 0, $width, $height, 100);
        imagesavealpha($playerImage, true);
        imagepng($playerImage, $this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png");

        imagedestroy($playerImage);
        imagedestroy($modelImage);
    }

    /**
     * @param Player $player
     * @param string $resource
     */
    public function sendImageSkin(Player $player, string $resource): void{
        $path = $this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png";
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
        $skin = new Skin($player->getSkin()->getSkinId(), $skinBytes, "", "geometry." . $resource, file_get_contents($this->plugin->getDataFolder() . "models" . DIRECTORY_SEPARATOR . $resource . ".json"));
        $this->broadcastChangeSkin($player, $skin);
    }

    /**
     * @param Player $player
     * @param Skin $skin
     */
    public function broadcastChangeSkin(Player $player, Skin $skin): void{
        $player->setSkin($skin);
        foreach (Server::getInstance()->getOnlinePlayers() as $players) {
            $player->sendSkin([$players, $player]);
        }
    }

    /**
     * @param Player $player
     */
    public function deleteSkinImage(Player $player): void{
        if (file_exists($this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png")) {
            unlink($this->plugin->getDataFolder() . "skins" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png");
        }
        if (file_exists($this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png")) {
            unlink($this->plugin->getDataFolder() . "images" . DIRECTORY_SEPARATOR . $player->getLowerCaseName() . ".png");
        }
        if (isset(self::$skins[$player->getLowerCaseName()])) {
            unset(self::$skins[$player->getLowerCaseName()]);
        }
    }

    /**
     * @param Player $player
     */
    public function callbackSkin(Player $player): void{
        if (($skin = $this->getPlayerSkin($player)) instanceof Skin) {
            $this->broadcastChangeSkin($player, $skin);
        }
    }
}