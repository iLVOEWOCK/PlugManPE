<?php

namespace woccck\PlugMan\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat as C;
use woccck\PlugMan\PlugManPE;
use woccck\PlugMan\Utils\Utils;

class PlugManCommand extends Command implements PluginOwned {

    public PlugManPE $plugin;

    public function __construct()
    {
        parent::__construct("plugman", "Manage plugins", "/plugman help", ["pm"]);
        $this->setPermission("plugmanpe.command");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::DARK_RED . "You must run this command in-game!");
            return false;
        }

        if (empty($args)) {
            Utils::sendPlugManForm($sender);
            return false;
        }

        $subcommand = array_shift($args);

        switch ($subcommand) {
            case "help":
                $sender->sendMessage(C::GRAY . "--------------------- [" . C::GREEN . " PlugMan " . C::GRAY . "] ---------------------\n" . C::GRAY . "- " . C::GREEN . "/plugman help " . C::WHITE . "- " . C::GRAY . "displays this.\n" . C::GRAY . "- " . C::GREEN . "/plugman list " . C::WHITE . "- " . C::GRAY . "List all plugins.\n" . C::GRAY . "- " . C::GREEN . "/plugman info " . C::WHITE . "- " . C::GRAY . "Gets info on a plugin.\n" . C::GRAY . "- " . C::GREEN . "/plugman reload " . C::WHITE . "- " . C::GRAY . "Reloads all the plugin configurations.\n");
                break;
            case "list":
                if ($sender->hasPermission("plugmanpe.list")) {
                    Utils::sendConfigListForm($sender, Utils::getAllServerPlugins());
                }
                break;
            case "info":
                $sender->sendMessage("info sub placeholder");
                break;
            case "reload":
                if ($sender->hasPermission("plugmanpe.reload")) {
                    $sender->sendMessage(C::GREEN . "Successfully reloaded all configurations");
                    Utils::reloadAllConfigurations();
                }
                break;
            default:
                Utils::sendPlugManForm($sender);
                break;
        }

        Utils::sendPlugManForm($sender);
        return true;
    }

    public function getOwningPlugin(): PlugManPE
    {
        return $this->plugin;
    }
}
