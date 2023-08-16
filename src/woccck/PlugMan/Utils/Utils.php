<?php

namespace woccck\PlugMan\Utils;

use FilesystemIterator;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use woccck\PlugMan\libs\jojoe77777\FormAPI\CustomForm;
use woccck\PlugMan\libs\jojoe77777\FormAPI\SimpleForm;
use woccck\PlugMan\PlugManPE;

class Utils
{

    public static function getPluginConfigurations(): array
    {
        $server = Server::getInstance();
        $plugins = $server->getPluginManager()->getPlugins();
        $pluginConfigurations = [];

        foreach ($plugins as $plugin) {
            $dataFolder = $plugin->getDataFolder();
            $yamlFiles = glob($dataFolder . "*.yml");
            if (!empty($dataFolder) && !empty($yamlFiles)) {
                $pluginConfigurations[$plugin->getName()] = $yamlFiles;
            }
        }

        return $pluginConfigurations;
    }

    public static function scanDirectory(string $dir): array
    {
        $entries = scandir($dir);
        $result = [];

        foreach ($entries as $entry) {
            if ($entry !== "." && $entry !== "..") {
                $result[] = $entry;
            }
        }

        return $result;
    }

    public static function sendPlugManForm(Player $player): void
    {
        $form = new SimpleForm(function (Player $player, ?int $data) {
            if ($data !== null) {
                $pluginConfigurations = self::getPluginConfigurations();
                $selectedPlugin = array_keys($pluginConfigurations)[$data];
                self::sendConfigListForm($player, $selectedPlugin);
            }
        });

        $form->setTitle("Plugin Configurations");
        $form->setContent("Select a plugin to view its configuration files:");

        $pluginConfigurations = self::getPluginConfigurations();

        foreach ($pluginConfigurations as $pluginName => $configFiles) {
            $form->addButton($pluginName);
        }

        $player->sendForm($form);
    }

    public static function sendConfigListForm(Player $player, string $pluginName): void
    {
        $plugin = Server::getInstance()->getPluginManager()->getPlugin($pluginName);
        if ($plugin === null) {
            $player->sendMessage(TextFormat::DARK_RED . "Plugin '$pluginName' not found.");
            return;
        }

        $dataFolder = $plugin->getDataFolder();
        $form = new SimpleForm(function (Player $player, ?int $data) use ($pluginName, $dataFolder) {
            if ($data !== null) {
                $selectedFiles = self::scanDirectory($dataFolder);
                $configName = $selectedFiles[$data];
                $configName = str_replace($dataFolder, '', $configName);
                self::sendConfigViewForm($player, $pluginName, $configName);
            }
        });

        $form->setTitle("Plugin Configurations");
        $form->setContent("Select a configuration to modify:");

        $selectedFiles = self::scanDirectory($dataFolder);

        foreach ($selectedFiles as $configName) {
            $form->addButton($configName);
        }

        $player->sendForm($form);
    }

    public static function sendConfigViewForm(Player $player, string $pluginName, string $configName): void
    {
        $configName = ltrim($configName, DIRECTORY_SEPARATOR);
        $dataFolder = Server::getInstance()->getPluginManager()->getPlugin($pluginName)->getDataFolder();
        $dataFolder = rtrim($dataFolder, DIRECTORY_SEPARATOR); // Remove trailing directory separator
        $configPath = $dataFolder . DIRECTORY_SEPARATOR . $configName;

        if (is_file($configPath)) {
            self::sendConfigEditForm($player, $pluginName, $configPath, basename($configPath));
        } else {
            $configFiles = self::scanConfigFiles($configPath);

            $form = new SimpleForm(function (Player $player, ?int $selectedOption) use ($configFiles, $configPath, $pluginName) {
                if ($selectedOption !== null) {
                    $selectedConfigName = $configFiles[$selectedOption];
                    $selectedConfigPath = $configPath . DIRECTORY_SEPARATOR . $selectedConfigName;
                    self::sendConfigEditForm($player, $pluginName, $selectedConfigPath, $selectedConfigName);
                }
            });

            $form->setTitle("Select Configuration File:");
            foreach ($configFiles as $configFile) {
                $form->addButton($configFile);
            }

            $player->sendForm($form);
        }
    }

    private static function scanConfigFiles(string $directory): array
    {
        $configFiles = [];

        if (is_dir($directory)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'yml') {
                    $relativePath = substr($file->getPathname(), strlen($directory) + 1);
                    $configFiles[] = $relativePath;
                }
            }
        }

        return $configFiles;
    }

    private static function sendConfigEditForm(Player $player, string $pluginName, string $configPath, string $configFileName): void
    {
        $configContents = file_get_contents($configPath);
        $configLines = explode("\n", $configContents);
        $allowNewLines = true;

        $form = new CustomForm(function (Player $player, ?array $data) use ($configContents, $configPath, $configLines) {
            if ($data !== null) {
                $modifiedConfigLines = [];
                foreach ($configLines as $line) {
                    if (strpos($line, ':') !== false) {
                        $parts = explode(':', $line, 2);
                        $key = trim($parts[0]);
                        $keyWithColon = $key . ':';
                        if (isset($data[$keyWithColon]) && trim($data[$keyWithColon]) !== '') {
                            $modifiedLine = $keyWithColon . ' ' . trim($data[$keyWithColon]);
                        } else {
                            $modifiedLine = $line;
                        }

                        $modifiedConfigLines[] = $modifiedLine;
                    } else {
                        $modifiedConfigLines[] = $line;
                    }
                }

                $modifiedConfig = implode("\n", $modifiedConfigLines);

                $player->sendMessage("§r§l§cOriginal Config:§f\n" . $configContents);
                $player->sendMessage("§r§l§aModified Config:§f\n" . $modifiedConfig);

                file_put_contents($configPath, $modifiedConfig);
                $player->sendMessage(TextFormat::GREEN . "Configuration '$configPath' has been updated.");
            }
        });

        foreach ($configLines as $line) {
            if (strpos($line, ':') !== false) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $keyWithColon = $key . ':';
                $defaultValue = isset($parts[1]) ? trim($parts[1]) : '';
                $form->addInput($keyWithColon, $defaultValue, null, $key . ':');
            }
        }

        $form->setTitle("Edit Configuration: $configFileName");
        $form->addLabel("Edit the configuration values:\n\nWarning: anything with `#`, `//` are usually for information do NOT modify\nunless you know exactly what you are doing.");

        $player->sendForm($form);
    }

    public static function reloadAllConfigurations(bool $showMessages = true): void {
        $plugins = Server::getInstance()->getPluginManager()->getPlugins();
        $hasConfig = false;

        $config = self::getPlugManConfig();
        $ignoredPlugins = $config->get("ignored-plugins", []);

        foreach ($plugins as $plugin) {
            if ($plugin instanceof PluginBase) {
                if (in_array($plugin->getName(), $ignoredPlugins, true)) {
                    continue;
                }
                $config = $plugin->getConfig();
                $config->reload();
                $pluginName = $plugin->getName();
                Server::getInstance()->getLogger()->info("Plugin '" . $pluginName . "' has reloaded all configurations.");
                $hasConfig = true;
                } else {
                if ($showMessages) {
                    $pluginName = $plugin->getName();
                    Server::getInstance()->getLogger()->warning("Plugin '$pluginName' does not have a configuration file.");
                }
            }
        }

        if (!$hasConfig && $showMessages) {
            Server::getInstance()->getLogger()->warning("No plugin configurations were found.");
        }
    }

    public static function getAllServerPlugins(): string
    {
        $server = Server::getInstance();
        $plugins = $server->getPluginManager()->getPlugins();
        $pluginNames = [];

        foreach ($plugins as $plugin) {
            $pluginNames[] = $plugin->getName();
        }

        $config = self::getMessagesConfig();
        $message = $config->get("list.list", "&r&9Plugins: {plugins}");
        $message = str_replace("{plugins}", implode(TextFormat::WHITE . ", ", $pluginNames), $message);

        return TextFormat::colorize($message);
    }

    public static function getPluginPerms(PluginBase $plugin) : array {
        $pluginPerms = [];
        foreach ($plugin->getDescription()->getPermissions() as $default => $perms) {
            foreach ($perms as $perm) {
                $pluginPerms[] = $perm;
            }
        }
        return $pluginPerms;
    }
    
    public static function getPlugManConfig() : Config {
        return new Config(PlugManPE::getInstance()->getDataFolder() . "config.yml", Config::YAML);
    }

    public static function getMessagesConfig() : Config {
        return new Config(PlugManPE::getInstance()->getDataFolder() . "messages.yml", Config::YAML);
    }
}
