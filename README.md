# Matomo BotTracker Plugin

Are you tracking data full of bots? That traffic is normally not useful for you, it is just clutter. Bot Tracker removes those visits from your normal data, and also provide separate reports so you could see which bots are visiting your site.

## Description

BotTracker is a plugin to *exclude* and separately *track* the visits of Bots, Spiders and Web Crawlers, that hit your site. Because Matomo doesn't store the user agent, BotTracker will only be able to track new bots from the moment you add them to its list forward (retroactive tracking isn't possible).

Many web crawlers, spiders and bots don't load the images in a page and don't execute JavaScript. So you cannot track them with Matomo if you don't use the PHP-API. The BotTracker can only track those that were caught by Matomo itself. With that said, many crawlers today are using headless browsers, and they do execute JavaScript.

### How it works

The plugin scans the user agent of any incoming visit for specific keywords. If the keyword is found, the visit is excluded from the normal log and the corresponding counter in the bot-table (bot_db) is increased.

If you enable the "extra stats" for a bot entry, the visit will also be written into a second bot-table (bot_db_stats). This second table logs the timestamp, the visited page and the user agent. This table is exposed in the report Bot Tracker: Extra stats.

You can add/delete/modify the keywords in Administration -> System -> Bot Tracker.

Source of Bots, Crawlers, Scrapers etc.:

* <https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json>
* <https://radar.cloudflare.com/traffic/verified-bots>
* <https://darkvisitors.com/>

### Installation / Update

See <http://Matomo.org/faq/plugins/#faq_21>

## License

GPL v3 / fair use

## Matomo Plugins by Digitalist Open Tech

This plugin was created by [Thomas--F](https://github.com/Thomas--F) and was taken over by Digitalist as part of contributing back with Matomo 5 upgrades.

For more information about plugins provided by Digitalist, see [our plugin page](https://github.com/digitalist-se/MatomoPlugins).
