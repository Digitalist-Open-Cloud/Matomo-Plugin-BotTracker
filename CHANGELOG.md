# Changelog

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) from version 5.3.0 (not released yet).

From version 5.x.x, major version reflects which Matomo version is supported.

## [5.2.14] - 2024-06-11

### Added

- Documentation and patch for using Log Analytics with Bot Tracker.

## [5.2.12] - 2024-05-27

### Added

- ClaudeBot to default bots

## [5.2.11] - 2024-04-14

### Fixed

* Adding a check if Bot Tracker config `track_device_detector_bots` exists, this missing check caused PHP errors. Fixes [#12](https://github.com/digitalist-se/BotTracker/issues/12).

## [5.2.10] - 2024-04-08

### Fixed

* Problem with translations and admin menu.

## [5.2.9] - 2024-04-08

### Added

* Bot Tracker now has it own admin menu, Administration -> Bot Tracker -> Configuration.
* Documentation is found at Administration -> Bot Tracker -> Documentation.
* Bots not configured, but found with [Matomo DeviceDetector](https://github.com/matomo-org/device-detector), can now be collected with a system setting. System -> General settings -> Bot Tracker -> Enable logging of non configured bots. Report "Bot Tracker: Other bots" shows these if the setting is activated. The user agent strings of these bots could be used for adding new Bots to track.

### Fixed

* Row `name` in `bot_type` database table is changed to varchar(256), varchar(255) was a typo.

### Changed

* Changelog format changed to [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) format.

## [5.2.0] - 2024-03-22

This is a big update, with many changes in code, new reports, new default bots added etc. To get the new default bots, just import default bots again, only the new ones will be added. Some preparations for 5.3.0 release added, that is not supported yet - categorisation of bots with type. Tables are added in this version, but they really do not have any purpose yet.

### Changed

* `functions.php` removed
* File `botlist.txt` removed, hard to keep up to date and out of the scope for this plugin.
* File `CHANGELOG.md` format changed to follow Markdown standard.

Database schema changes:

* Table `bot_db`: `botName` and `botKeyword` could now be 256 chars long.
* Table `bot_db_stat`: `page` could now be 256 chars long.
* Table `bot_type` added.
* Column `botType` added in table `bot_db`
* Table `bot_visits` added.

### Deprecated

* Bot visits will now be tracked in table bot_visits, and use of visits in `bot_db` is deprecated, and will be removed in 5.3.0. This change is done so reports of bots could be based on dates, ranges etc. As the old format only allowed to show the total. Old reports will stay until 5.3.0, and are marked as deprecated in code and in UI. This change will increase database size, as every defined bot visit will get a database row for a visit, therefor the new table is kept to absolute minimum.

### Added

#### Reports

* Bot Tracker: Report - shows all bots visits in chosen time frame.
* Bot Tracker: Top 10 robots - a pie chart with the ten most frequent bots in chosen time frame.
* Bot Tracker: Extra stats - if extra stats is enabled for a bot, you get all visits by the bots in chosen time frame.

#### Cli Commands

* Cli Commands added for simpler administration and automation.
  * `bottracker:add-bot`
  * `bottracker:add-bot-type` (does not have a purpose yet)
  * `bottracker:add-default-bots`
  * `bottracker:delete-bot`
  * `bottracker:list-bot-types` (does not have a purpose yet)
  * `bottracker:list-bots`

#### Testing

Some basic unit och integration tests were added.

## [5.0.1] - unknown date

* Removed `logToFile` function.

## [v3.0.0] - 2023-11-28

Matomo 5 compatibility fixes.

## [v2.08] - 2023-02-18

* translation-updates (issue #97)
* new OK-icon with transparency (issue #98)
* fix deprecated dynamic properties (issue #99)

## [v2.07] - 2022-02-27

* translation-updates (issue #94)

## [v2.06] - 2022-01-30

* fix for the archive-problem (issue #87)

## [v2.05] - 2021-10-29

* fix a problem in the api.php after the changes in v2.04 (issue #84)

## [v2.04] - 2021-10-28

* Fix plugin does not work when used in Matomo for WordPress (issue #83)
* a bunch of translation-updates (issue #81)

## [2.03] - 2021-05-30

* assure that useragent length limit is kept for extra stats table (issue #73)

## [v2.02] - 2021-05-17

* fix for issue #70

## [v2.01] - 2021-04-25

* change order of columns in the BotTracker report (issue #68)

## [2.00] - 2020-12-05

* upgrade to Matomo 4 (issue #66)

## [1.07] - unknown date

* correct PHP notice on line 114 (issue #65)

## [1.06] - 2019-08-27

* correct default for "botLastVisit" (issue #63)

## [1.05] - 2019-07-25

* removed default on visit_timestamp (issue #53)
* changed primary key and add aditional column for stats table (issue #53)
* changed default for last_visit (issue #61)
* corrected delimiter in botlist.txt (issue #62)

## [1.04] - 2018-09-09

* change license string (validator-fail)

## [1.03] - 2018-09-09

* replace deprecated functions

## [1.02] - 2016-12-15

* change PHP-requirements for Piwik v3

## [1.01] - 2016-11-03

* changes at description and changelog for Piwik v3

## [1.00] - 2016-11-02

* upgrade to Piwik Version 3 (issue #50)
* some parts were new coded, others are only migrated

## [0.58] - 2016-10-21

* new feature: BotTracker now works with the import_logs-script (issue #38)
* add: some new translation-strings (issue #46)
* bufgix: truncate the url to max 100 bytes (issue #49)

## [0.57] - 2015-11-06

* bugfix: change of order and position in the BotTracker-Visitor-View
* deleting of the old update-scripts (from version 0.43 and 0.45)
* bugfix: change of the default-value for botLastVisit '0000-00-00' to '2000-01-01'
* new feature: file import for new bots (see online-help in the administration-dialog for more infos)

## [0.56] - 2015-09-27

* bugfix: botLastVisit-Date is not shown (pull request #35)
* bugfix: Some characters are not quoted properly (issue #32)
* a lot more languages. Thanks a lot to all transiflex-supporter

## [0.55] - 2015-05-23

* some minor bugfixes and typos
* add some more languages

## [0.54] - 2015-01-28

* bugfix for Piwik 2.11

## [0.53] - 2014-12-17

* bugfix for cloud-view on "Top 10"
* deactivating insights for "Top 10"
* add more default bots (just use the "add default bots" button, only the new ones will be added)

## [0.52] - 2014-09-04

* bugfix for issue #10 (NOTICE in error-log for undeclared variables)

## [0.51] - 2014-08-07

* emergency-fix for v0.50

## [0.50] - 2014-08-07

* bugfix for issue #9 (wrong time zone for last visit)

## [0.49] - 2014-06-22

* fixed crash with a new and empty webpage

## [0.48] - 2014-06-13

* change requirements because 0.47 doesn't work with Piwik 2.3

## [0.47] - 2014-06-13

* bugfix: changes menu-creation for Piwik v2.4

## [0.46] - 2014-04-14

* bugfix: remove depricated method for Piwik v2.2

## [0.45] - 2014-04-03

* add column to primary key in extra-stats-table

## [0.44] - 2014-02-21

* more description for the marketplace

## [0.43] - 2014-02-21

* Compatible with Piwik 2.0

