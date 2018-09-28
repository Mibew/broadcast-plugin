# Broadcast plugin

A plugin to broadcast a message.

At the moment there are four broadcasting modes available:

* to all active chats (i.e. to chats with an operator connected);
* to all waiting chats (i.e. to chats in a queue and chats where an operator has been disconnected);
* to all chats except for closed ones;
* to all operators involved in chats (i.e. to operators in active chats or in active invitations).

Messages being sending as system ones. To send a message one should be an administrator.

Plugin's localization constants could be translated via standard interface.

## Installation

1. Get the archive with the plugin sources. You can download it from the
[official site](https://mibew.org/plugins#mibew-broadcast) or build the
plugin from sources.

2. Untar/unzip the plugin's archive.

3. Put files of the plugins to the `<Mibew root>/plugins` folder.

4. Navigate to "`<Mibew Base URL>`/operator/plugin" page and enable the plugin.

## Build from sources

There are several actions one should do before use the latest version of the plugin from the repository:

1. Obtain a copy of the repository using `git clone`, download button, or another way.
2. Install [node.js](http://nodejs.org/) and [npm](https://www.npmjs.org/).
3. Install [Gulp](http://gulpjs.com/).
4. Install npm dependencies using `npm install`.
5. Run Gulp to build the sources using `gulp default`.

Finally `.tar.gz` and `.zip` archives of the ready-to-use Plugin will be available in `release` directory.

## License

[Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0.html)
