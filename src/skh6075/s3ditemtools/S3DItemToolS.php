<?php

namespace skh6075\s3ditemtools;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use skh6075\s3ditemtools\skin\PlayerSkin;
use skh6075\s3ditemtools\skin\SkinFactory;
use skh6075\s3ditemtools\skin\SkinReflection;
use function mkdir;

final class S3DItemToolS extends PluginBase implements Listener{
    use SingletonTrait;

    protected function onLoad(): void{
        self::setInstance($this);
    }

    protected function onEnable(): void{
        mkdir($this->getDataFolder() . "models/");
        mkdir($this->getDataFolder() . "images/");
        mkdir($this->getDataFolder() . "skins/");

        SkinFactory::getInstance()->init();

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /** @priority LOWEST */
    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $player = $event->getPlayer();

        PlayerSkin::getInstance()->makeSkinImage($player);
        PlayerSkin::getInstance()->setPlayerSkin($player);
    }

    /** @priority MONITOR */
    public function onPlayerQuit(PlayerQuitEvent $event): void{
        PlayerSkin::getInstance()->deleteSkinImage($event->getPlayer());
    }

    /** @priority LOWEST */
    public function onPlayerItemHeld(PlayerItemHeldEvent $event): void{
        $player = $event->getPlayer();
        $item   = $event->getItem();

        if (!$item->getNamedTag()->getTag("3d_model") instanceof StringTag)
            return;

        $value = $item->getNamedTag()->getString("3d_model");
        if (!($skin = SkinFactory::getInstance()->getSkin($value)) instanceof SkinReflection) {
            PlayerSkin::getInstance()->callbackSkin($player);
        } else {
            $skin->sendSkinImage($player);
        }
    }
}