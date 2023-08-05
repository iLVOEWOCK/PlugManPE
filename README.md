# PlugManPE

PlugManPE is a simple, easy-to-use plugin that allows server admins to manage plugins directly from the game or console without the need to restart the server. This port is designed for PocketMine-MP servers.

## Features

- Enable, disable, load, and unload plugins in real-time without restarting the server.
- List all installed plugins along with their status (enabled, disabled, loaded, or unloaded).
- View detailed information about each plugin, including version, author, description, and more.
- Easily manage your plugins with intuitive commands and an in-game UI.

## Installation

1. Download the latest release of PlugManX from the [releases page](https://github.com/iLVOEWOCK/PlugManPE/releases).
2. Copy the downloaded `PlugManPE.phar` file into your PocketMine server's `plugins` folder.
3. Restart your PocketMine server to load the plugin.

## Usage

### In-Game Commands

- `/plugman` - Display the main PlugManX command menu.
- `/plugman list` - List all installed plugins and their status.
- `/plugman info <plugin>` - View detailed information about a specific plugin.
- `/plugman enable <plugin>` - Enable a plugin.
- `/plugman disable <plugin>` - Disable a plugin.
- `/plugman reload <plugin>` - Load a plugin.

## Permissions

- `plugmanpe.command` - Required to use all PlugManX commands.

## Support

If you encounter any issues or have questions, feel free to create an issue on the [GitHub repository](https://github.com/iLVOEWOCK/PlugManPE/issues).

## Contributing

Contributions to PlugManX are welcome! If you have any improvements, bug fixes, or new features, please submit a pull request.

## To Do

- [ ] properly create list subcommand
- [ ] properly create info subcommand
- [ ] create enable subcommand
- [ ] create disable subcommand

    
- [ ] messages.yml (maybe)

## License

PlugManX is open-source and licensed under the [GPL License](LICENSE).
