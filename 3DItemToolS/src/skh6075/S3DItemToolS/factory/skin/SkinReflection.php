<?php

namespace skh6075\S3DItemToolS\factory\skin;

use pocketmine\Player;
use skh6075\S3DItemToolS\factory\player\PlayerSkin;

class SkinReflection{

    private $name;


    public function __construct (string $name) {
        $this->name = $name;
    }

    public function getName(): string{
        return $this->name;
    }

    public function sendSkinImage(Player $player): void{
        PlayerSkin::convertImageMerge($player, $this->name);
        PlayerSkin::sendImageSkin($player, $this->name);
    }
}