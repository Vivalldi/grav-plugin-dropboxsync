# [Grav DropBox Sync Plugin][project]

> This plugin syncs your data with your DropBox account.

## About

TODO

## Installation and Updates

Installing or updating the `DropBox Sync` plugin can be done in one of two ways. Using the GPM (Grav Package Manager) installation method or manual install or update method by downloading [this plugin][project] and extracting all plugin files to

	/your/site/grav/user/plugins/dropboxsync

For more informations, please check the [Installation and update guide](docs/INSTALL.md).

## Usage

The `DropBox Sync` plugin comes with some sensible default configuration, that are pretty self explanatory:

### Config Defaults

```yaml
# Global plugin configurations

enabled: true                 # Set to false to disable this plugin completely

# Default options for DropBox Sync configuration.

app:
  key: "yourappkey"           # DropBox API key
  secret: "yourappsecret"     # DropBox API secret

# Global and page specific configurations
```

If you need to change any value, then the best process is to copy the [dropboxsync.yaml](dropboxsync.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there. This will override the default settings.

If you want to alter the settings for one or a few pages only, you can do so by adding page specific configurations into your page headers, e.g.

```yaml
dropboxsync:
	enabled: false
```

to disable the `DropBox Sync` plugin just for this page.

## Contributing

You can contribute at any time! Before opening any issue, please search for existing issues and review the [guidelines for contributing](docs/CONTRIBUTING.md).

After that please note:

* If you find a bug or would like to make a feature request or suggest an improvement, [please open a new issue][issues]. If you have any interesting ideas for additions to the syntax please do suggest them as well!
* Feature requests are more likely to get attention if you include a clearly described use case.
* If you wish to submit a pull request, please make again sure that your request match the [guidelines for contributing](docs/CONTRIBUTING.md) and that you keep track of adding unit tests for any new or changed functionality.

## License

Copyright (c) 2015 [Tyler Cosgrove][github-tc] and [Benjamin Regler][github-br]. See also the list of [contributors] who participated in this project.

[Licensed](LICENSE) for use under the terms of the [MIT license][mit-license].

[github-tc]: https://github.com/Vivalldi/ "GitHub account of Vivalldi"
[github-br]: https://github.com/Sommerregen/ "GitHub account of Sommerregen"
[mit-license]: http://www.opensource.org/licenses/mit-license.php "MIT license"

[project]: https://github.com/Vivalldi/grav-plugin-dropboxsync
[issues]: https://github.com/Vivalldi/grav-plugin-dropboxsync/issues "GitHub Issues for Grav DropBox Sync Plugin"
[contributors]: https://github.com/Vivalldi/grav-plugin-dropboxsync/graphs/contributors "List of contributors of the project"
