# Changelog

## 1.3.0

* Update dependencies

## 1.2.0

 * Add: Rename plugins by clicking on their title
 * Add: Prevents redirecting away from plugins.php when activating plugins
 * Add: Hides "uninstall" links
 * Add: GitHub Updater support
 * Add: Logger to catch errors

## 1.1.2

* Requires PHP 8.0 for DOMDocument properties.

## 1.1.1

* Refactored logic into Parsed_Link class

## 1.1.0

* Add: Prevent plugins from redirecting away from plugins.php when installed
* Fix: JetPack is calling plugin functions with null parameters
* Project structure changed

## 1.0.9 2022-04-27

* Refactoring, testing and linting.

## 1.0.8

* Fix: No longer throws errors on malformed HTML &.

## 1.0.7 2012-09-15

* Fix bug when a plugin was providing an empty link ( just '' !).

## 1.0.6 2021-09-13

* Pass all plugin data to apply_filters (some plugins rely on it and were logging errors)
