<?php

namespace skh6075\S3DItemToolS\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\StringTag;
use skh6075\S3DItemToolS\factory\player\PlayerSkin;
use skh6075\S3DItemToolS\factory\skin\SkinReflection;
use skh6075\S3DItemToolS\factory\SkinFactory;

class EventListener implements Listener{


    public function handlePlayerJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        PlayerSkin::makeSkinImage($player);
        PlayerSkin::setPlayerSkin($player);
    }

    public function handlePlayerQuit(PlayerQuitEvent $event): void{
        $player = $event->getPlayer();
        PlayerSkin::resetSkinImage($player);
    }

    public function handlePlayerItemHeld(PlayerItemHeldEvent $event): void{
        $player = $event->getPlayer();
        $item = $event->getItem();

        if (!is_null($item->getNamedTagEntry('3d_model'))) {
            $name = $item->getNamedTagEntry('3d_model')->getValue();
            if (($class = SkinFactory::getSkinReflection($name)) instanceof SkinReflection) {
                $class->sendSkinImage($player);
            } else {
                PlayerSkin::callbackSkin($player);
            }
        }
    }
}