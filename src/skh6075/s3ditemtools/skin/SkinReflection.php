<?php

namespace skh6075\s3ditemtools\skin;

use pocketmine\Player;

class SkinReflection{

    /** @var string */
    protected $name;


    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getName(): string{
        return $this->name;
    }

    /**
     * @param Player $player
     */
    public function sendSkinImage(Player $player): void{
        PlayerSkin::getInstance()->convertImagemerge($player, $this->name);
        PlayerSkin::getInstance()->sendImageSkin($player, $this->name);
    }
}