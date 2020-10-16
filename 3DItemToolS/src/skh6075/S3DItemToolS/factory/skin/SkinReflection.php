<?php

namespace skh6075\S3DItemToolS\factory\skin;

use pocketmine\Player;
use skh6075\S3DItemToolS\factory\player\PlayerSkin;

class SkinReflection{

    private $name;


    public function __construct (string $name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string{
        return $this->name;
    }

    /**
     * @param Player $player
     */
    public function sendSkinImage(Player $player): void{
        PlayerSkin::getInstance ()->convertImageMerge($player, $this->name);
        PlayerSkin::getInstance ()->sendImageSkin($player, $this->name);
    }
}
