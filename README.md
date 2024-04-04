# Matomo Bot Tracker Plugin

Are you tracking data full of bots? That traffic is normally not useful for you, it is just clutter. Bot Tracker removes those visits from your normal data, and also provide separate reports so you could see which bots are visiting your site. With Matomo and Bot Tracker you have insight in Bot Traffic on your site.

## Description

Bot Tracker is a plugin to *exclude* and separately *track* the visits of Bots, Spiders and Web Crawlers, that hit your site. Because Matomo doesn't store the user agent, Bot Tracker will only be able to track new bots from the moment you add them to its list forward (retroactive tracking isn't possible).

Many web crawlers, spiders and bots don't load the images in a page and don't execute JavaScript. So you cannot track them with Matomo if you don't use the PHP-API. The Bot Tracker can only track those that were caught by Matomo itself. With that said, many crawlers today are using headless browsers, and they do execute JavaScript.

### How it works

The plugin scans the user agent of any incoming visit for specific keywords. If the keyword is found, the visit is excluded from the normal log and logged separately in Bot Tracker reports.

If you enable the "extra stats" for a bot entry, you will get more in depth data about the Bots visit, and you will get this in the widget Bot Tracker: Extra stats.

You can add/delete/modify the keywords in Administration -> Bot Tracker -> Configuration.

### Track bots identified with Device Detector

As additional tracking of bots, you can collect the bots identified with Matomos Device Detector, either with activating the setting in Administration -> General settings -> Bot Tracker, or with setting this in `config.ini.php`:

```php
[BotTracker]
track_device_detector_bots = 1
```

This is for collecting data for identified bots user agents, which you could use for setting up more in detail tracking with Bot Tracker.

### Installation / Update

See <https://matomo.org/faq/plugins/faq_21/>

### Sources of information for Bots, Crawlers, Scrapers etc

* <https://raw.githubusercontent.com/monperrus/crawler-user-agents/master/crawler-user-agents.json>
* <https://radar.cloudflare.com/traffic/verified-bots>
* <https://darkvisitors.com/>
* <https://badbot.org/>
* <https://udger.com/resources/ua-list/crawlers>

## License

GPL v3 / fair use

## Matomo Plugins by Digitalist Open Tech

This plugin was created by [Thomas--F](https://github.com/Thomas--F) and was taken over by Digitalist as part of contributing back with Matomo 5 upgrades.

For more information about plugins provided by Digitalist, see [our plugin page](https://github.com/digitalist-se/MatomoPlugins).
