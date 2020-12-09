<?php

namespace skh6075\s3ditemtools\listener;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use skh6075\s3ditemtools\skin\PlayerSkin;
use skh6075\s3ditemtools\skin\SkinFactory;
use skh6075\s3ditemtools\skin\SkinReflection;

class EventListener implements Listener{

    /**
     * @param PlayerJoinEvent $event
     * @priority HIGHEST
     */
    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();
        PlayerSkin::getInstance()->makeSkinImage($player);
        PlayerSkin::getInstance()->setPlayerSkin($player);
    }

    /**
     * @param PlayerQuitEvent $event
     * @priority HIGHEST
     */
    public function onPlayerQuit(PlayerQuitEvent $event): void{
        PlayerSkin::getInstance()->deleteSkinImage($event->getPlayer());
    }

    public function onPlayerItemdHeld(PlayerItemHeldEvent $event): void{
        $player = $event->getPlayer();
        $item   = $event->getItem();

        if (!is_null($item->getNamedTagEntry("3d_model"))) {
            if (($reflection = SkinFactory::getInstance()->getSkinReflection($item->getNamedTagEntry("3d_model")->getValue())) instanceof SkinReflection) {
                $reflection->sendSkinImage($player);
            } else {
                PlayerSkin::getInstance()->callbackSkin($player);
            }
        }
    }
}