# Bot Tracker Changelog

## 5.2.9

### Added

* Documentation is found at Administration -> Bot Tracker -> Documentation.
* Bots not configured, but found with Matomo DeviceDetector (used by Matomo core), can now be collected with a system setting. System -> General settings -> Bot Tracker -> Enable logging of non configured bots, or set in config file (see Readme).
* Report and widget "Bot Tracker: Other Bots" shows these if the setting is activated. The user agent strings of these bots could be used for adding a new Bots to track.

### Changed

* Bot Tracker admin moved to own menu, Administration -> Bot Tracker -> Configuration.

## 5.2.0

This is a big update, with many changes in code, new reports, new default bots added etc. To get the new default bots, just import default bots again, only the new ones will be added. Some preparations for 5.3.0 release added, that is not supported yet - categorisation of bots with type. Tables are added in this version, but they really do not have any purpose yet.

### Breaking changes

* `functions.php` removed
* File `botlist.txt` removed, hard to keep up to date and out of the scope for this plugin.
* File `CHANGELOG.md` format changed to follow Markdown standard.

### Deprecations

* Bot visits will now be tracked in table bot_visits, and use of visits in `bot_db` is deprecated, and will be removed in 5.3.0. This change is done so reports of bots could be based on dates, ranges etc. As the old format only allowed to show the total. Old reports will stay until 5.3.0, and are marked as deprecated in code and in UI. This change will increase database size, as every defined bot visit will get a database row for a visit, therefor the new table is kept to absolute minimum.

### Changes

#### Schema

* Table `bot_db`: `botName` and `botKeyword` could now be 256 chars long.
* Table `bot_db_stat`: `page` could now be 256 chars long.
* Table `bot_type` added.
* Column `botType` added in table `bot_db`
* Table `bot_visits` added.

### Added

#### New reports

* Bot Tracker: Report - shows all bots visits in chosen time frame.
* Bot Tracker: Top 10 robots - a pie chart with the ten most frequent bots in chosen time frame.
* Bot Tracker: Extra stats - if extra stats is enabled for a bot, you get all visits by the bots in chosen time frame.

#### New functions

* Cli Commands added for simpler administration and automation.
  * `bottracker:add-bot`
  * `bottracker:add-bot-type` (does not have a purpose yet)
  * `bottracker:add-default-bots`
  * `bottracker:delete-bot`
  * `bottracker:list-bot-types` (does not have a purpose yet)
  * `bottracker:list-bots`

#### Testing

Some basic unit och integration tests are added.

## 5.0.1

* Removed `logToFile` function.

## 3.0.0

Matomo 5 compatibility fixes.

## 2.08

* translation-updates (issue #97)
* new OK-icon with transparency (issue #98)
* fix deprecated dynamic properties (issue #99)

## 2.07

* translation-updates (issue #94)

## 2.06

* fix for the archive-problem (issue #87)

## 2.05

* fix a problem in the api.php after the changes in v2.04 (issue #84)

## 2.04

* Fix plugin does not work when used in Matomo for WordPress (issue #83)
* a bunch of translation-updates (issue #81)

## 2.03

* assure that useragent length limit is kept for extra stats table (issue #73)

## 2.02

* fix for issue #70

## 2.01

* change order of columns in the BotTracker report (issue #68)

## 2.00

* upgrade to Matomo 4 (issue #66)

## 1.07

* correct PHP notice on line 114 (issue #65)

## 1.06

* correct default for "botLastVisit" (issue #63)

## 1.05

* removed default on visit_timestamp (issue #53)
* changed primary key and add aditional column for stats table (issue #53)
* changed default for last_visit (issue #61)
* corrected delimiter in botlist.txt (issue #62)

## 1.04

* change license string (validator-fail)

## 1.03

* replace depricated functions

## 1.02

* change PHP-requirements for Piwik v3

## 1.01

* changes at description and changelog for Piwik v3

## 1.00

* upgrade to Piwik Version 3 (issue #50)
* some parts were new coded, others are only migrated

## 0.58

* new feature: BotTracker now works with the import_logs-script (issue #38)
* add: some new translation-strings (issue #46)
* bufgix: truncate the url to max 100 bytes (issue #49)

## 0.57

* bugfix: change of order and position in the BotTracker-Visitor-View
* deleting of the old update-scripts (from version 0.43 and 0.45)
* bugfix: change of the default-value for botLastVisit '0000-00-00' to '2000-01-01'
* new feature: file import for new bots (see online-help in the administration-dialog for more infos)

## 0.56

* bugfix: botLastVisit-Date is not shown (pull request #35)
* bugfix: Some characters are not quoted properly (issue #32)
* a lot more languages. Thanks a lot to all transiflex-supporter

## 0.55

* some minor bugfixes and typos
* add some more languages

## 0.54

* bugfix for Piwik 2.11

## 0.53

* bugfix for cloud-view on "Top 10"
* deactivating insights for "Top 10"
* add more default bots (just use the "add default bots" button, only the new ones will be added)

## 0.52

* bugfix for issue #10 (NOTICE in error-log for undeclared variables)

## 0.51

* emergency-fix for v0.50

## 0.50

* bugfix for issue #9 (wrong time zone for last visit)

## 0.49

* fixed crash with a new and empty webpage

## 0.48

* change requirements because 0.47 doesn't work with Piwik 2.3

## 0.47

* bugfix: changes menu-creation for Piwik v2.4

## 0.46

* bugfix: remove depricated method for Piwik v2.2

## 0.45

* add column to primary key in extra-stats-table

## 0.44

* more description for the marketplace

## 0.43

* Compatible with Piwik 2.0

---

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) from version 5.3.0.