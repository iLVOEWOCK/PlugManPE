<?php

namespace woccck\PlugMan;

use pocketmine\plugin\PluginBase;
use woccck\PlugMan\Commands\PlugManCommand;

class PlugManPE extends PluginBase {

    public static PlugManPE $instance;

    public function onLoad(): void
    {
        self::$instance = $this;
    }

    public function onEnable(): void
    {
        $this->saveDefaultConfig();
        $this->registerCommands();
    }

    public function registerCommands() {
        $this->getServer()->getCommandMap()->registerAll("plugmanpe", [
            new PlugManCommand()
        ]);
    }

    public static function getInstance() : PlugManPE {
        return self::$instance;
    }
}
