# Matomo BotTracker Plugin

## Description

BotTracker ist a plugin to exclude and separately track the visits of bots, spiders and webcrawlers, that hit your page. Because Matomo doesn't store the user agent, BotTracker will only be able to track new bots from the moment you add them to its list forward (retroactive tracking isn't possible).

This plugin is still in BETA-status, but I have tested it for a while. It should be stable.

Before you install this plugin, here's something you should be aware of:
Many webcrawlers, spiders and bots don't load the images in a page and most of them don't execute JavaScript. So you cannot track them with Matomo if you don't use the PHP-API. The BotTracker can only track those that were caught by Matomo itself.

### How it works

The plugin scans the user agent of any incoming visit for specific keywords. If the keyword is found, the visit is excluded from the normal log and the corresponding counter in the bot-table (BOT_DB) is increased.
If you enable the "extra stats" for a bot entry, the visit will also be written into a second bot-table (BOT_DB_STAT). This second table logs the timestamp, the visited page and the user agent. The second table is currently not displayed in Matomo, but the more experienced users can select the data from the database. Some more detailed reports may come in the future.

You can add/delete/modify the keywords in the administration-menu. There are webpages that list the user-agents of known spiders and webcrawlers (e.g. <https://www.useragentstring.com/pages/useragentstring.php> ). The most common bots are already in the default list of the plugin.

### Installation / Update

See <http://Matomo.org/faq/plugins/#faq_21>

## License

GPL v3 / fair use

## Matomo Plugins by Digitalist

This plugin was created by [Thomas--F](https://github.com/Thomas--F) and was taken over
by Digitalist as part of contributing back with Matomo 5 upgrades.

For more information about plugins provided by Digitalist, see [our plugin page](https://github.com/digitalist-se/MatomoPlugins).
