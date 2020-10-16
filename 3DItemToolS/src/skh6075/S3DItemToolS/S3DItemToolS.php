<?php

namespace skh6075\S3DItemToolS;


use pocketmine\plugin\PluginBase;
use skh6075\S3DItemToolS\factory\SkinFactory;
use skh6075\S3DItemToolS\listener\EventListener;

class S3DItemToolS extends PluginBase{

    /** @var S3DItemToolS */
    private static $instance;


    public static function getInstance(): ?S3DItemToolS{
        return self::$instance;
    }

    public function onLoad(): void{
        self::$instance = $this;
        SkinFactory::init ();
    }

    public function onEnable(): void{
        $this->getServer ()
            ->getPluginManager ()
            ->registerEvents (new EventListener (), $this);
    }
}
