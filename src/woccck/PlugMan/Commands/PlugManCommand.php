<?php

namespace woccck\PlugMan\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextFormat as C;
use woccck\PlugMan\PlugManPE;
use pocketmine\plugin\PluginBase;
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
        $config = Utils::getMessagesConfig();
        if (!$sender instanceof Player) {
            $sender->sendMessage(C::DARK_RED . "You must run this command in-game!");
            return false;
        }

        if (empty($args)) {
            //Utils::sendPlugManForm($sender);
            return false;
        }

        $subcommand = array_shift($args);

        switch ($subcommand) {
            case "help":
                $sender->sendMessage(C::GRAY . "--------------------- [" . C::GREEN . " PlugMan " . C::GRAY . "] ---------------------\n" . C::GRAY  . "- " . C::GREEN . "/plugman " . C::WHITE . "- " . C::GRAY . "Base PlugManPE Command.\n" . "- " . C::GREEN . "/plugman help " . C::WHITE . "- " . C::GRAY . "displays this.\n" . C::GRAY . "- " . C::GREEN . "/plugman list " . C::WHITE . "- " . C::GRAY . "List all plugins.\n" . C::GRAY . "- " . C::GREEN . "/plugman info " . C::WHITE . "- " . C::GRAY . "Gets info on a plugin.\n" . C::GRAY . "- " . C::GREEN . "/plugman reload " . C::WHITE . "- " . C::GRAY . "Reloads all the plugin configurations.\n" . C::GRAY . "- " . C::GREEN . "/plugman listpermissions " . C::WHITE . "- " . C::GRAY . "List the permissions of a plugin.\n");
                break;
            case "list":
                if ($sender->hasPermission("plugmanpe.list")) {
                    $sender->sendMessage(Utils::getAllServerPlugins());
                }
                break;
            case "info":
                if ($sender->hasPermission("plugmanpe.info")) {
                    if (isset($args[0])) {
                        $pluginName = $args[0];
                        $plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName);
                        if ($plugin !== null) {
                            $infoPname = $config->getNested("info.header", "&r&fPlugin Information: {plugin}");
                            $infoPname = str_replace("{plugin}", $plugin->getName(), $infoPname);
                            $sender->sendMessage(C::colorize($infoPname));
                            if ($plugin->isEnabled()) {
                                $infoPstatus = $config->getNested("info.status.enabled", "&r&7- Status: &aenabled");
                                $sender->sendMessage(C::colorize($infoPstatus));
                            } else {
                                $infoPstatusoff = $config->getNested("info.status.disabled", "&r&7- Status: &4disabled");
                                $sender->sendMessage(C::colorize($infoPstatusoff));
                            }
                            $infoPversion = $config->getNested("info.version", "&r&7- Version: &a{version}");
                            $infoPversion = str_replace("{version}", $plugin->getDescription()->getVersion(), $infoPversion);
                            $sender->sendMessage(C::colorize($infoPversion));

                            $infoPauthors = $config->getNested("info.authors", "&r&7- Author(s): &a{authors}");
                            $authors = implode(", ", $plugin->getDescription()->getAuthors());
                            if (strlen($authors) === 0) {
                                $authors = C::DARK_RED . "none";
                            }
                            $infoPauthors = str_replace("{authors}", $authors, $infoPauthors);
                            $sender->sendMessage(C::colorize($infoPauthors));


                            $infoPdepend = $config->getNested("info.depend", "&r&7- Depend: &a{depend}");
                            $depend = implode(", ", $plugin->getDescription()->getDepend());
                            if (strlen($depend) === 0) {
                                $depend = C::DARK_RED . "none";
                            }
                            $infoPdepend = str_replace("{depend}", $depend, $infoPdepend);
                            $sender->sendMessage(C::colorize($infoPdepend));


                            $infoPsoftdepend = $config->getNested("info.softdepend", "&r&7- SoftDepend: &a{soft.depend}");
                            $softdepend = implode(", ", $plugin->getDescription()->getSoftDepend());
                            if (strlen($softdepend) === 0) {
                                $softdepend = C::DARK_RED . "none";
                            }
                            $infoPsoftdepend = str_replace("{soft.depend}", $softdepend, $infoPsoftdepend);
                            $sender->sendMessage(C::colorize($infoPsoftdepend));
                        } else {
                            $infoPnotfound = $config->getNested("info.notfound", "&r&cPlugin not found: '&f{plugin}&c`");
                            $infoPnotfound = str_replace("{plugin}", $pluginName, $infoPnotfound);
                            $sender->sendMessage(C::colorize($infoPnotfound));
                        }
                    } else {
                        $sender->sendMessage(C::RED . "Usage: /plugman info <plugin>");
                    }
                }
                break;
            case "reload":
                if ($sender->hasPermission("plugmanpe.reload")) {
                    $reloadAll = $config->getNested("reload.all", "&r&9All plugins have been reloaded.");
                    $sender->sendMessage(C::colorize($reloadAll));
                    Utils::reloadAllConfigurations();
                }
                break;
            case "listpermissions":
            case "lp":
                if ($sender->hasPermission("plugmanpe.listperms")) {
                    if (isset($args[0])) {
                        $pluginName = $args[0];
                        $plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName);
                        if ($plugin !== null) {
                            if ($plugin instanceof PluginBase)
                            $permissions = Utils::getPluginPerms($plugin);
                            if (empty($permissions)) {
                                $sender->sendMessage(C::colorize("&r&7Permissions: &cNone"));
                            } else {
                                $listPermsMessage = "&r&aPermissions:";
                                foreach ($permissions as $permission) {
                                    $permissionString = $permission->getName();
                                    $listPermsMessage .= "\n- " . $permissionString;
                                }

                                $sender->sendMessage(C::colorize($listPermsMessage));
                            }
                        }
                    } else {
                        $sender->sendMessage(C::RED . "Usage: /plugman listpermissions <plugin>");
                    }
                }
                break;
            default:
                //Utils::sendPlugManForm($sender);
                break;
        }
        return true;
    }


    public function getOwningPlugin(): PlugManPE
    {
        return $this->plugin;
    }
}
