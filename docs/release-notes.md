## Release Notes

### BootstrapComponents 5.0.1

Released on _not yet_

Changes:
* switch integration tests from travis to github workflows
* add translations via translatewiki

Fixes:
* fix template variables component attributes are not parsed
* documentation issues
* unit test's ExecutionTimeTestListener.php, thx @malberts
* integration test's JSON File Handler, thx @malberts
  (integration tests still broken, though)
* coverage report upload to scrutinizer

### BootstrapComponents 5.0.0

Released on 14-January-2023

Breaking changes:
* requires MediaWiki 1.35 or later
* requires PHP 7.4 or later

Fixes:
- fix component card's unit tests
- fix modal tests for REL1_31
- fix a destined-to-fail lua test
- remove smw from tests (and consequently deactivating integration tests)

### BootstrapComponents 4.0.1

Released on 26-May-2021

Changes:
* add translations via translatewiki
* bump bootstrap extension dependency to 4.5.x

Fixes:
* fix travis issues, thx @malberts
* fix MW1.35 deprecation, thx @malberts
* fix collapse active class, thx @malberts
* fix issues with PageForms file upload

### BootstrapComponents 4.0.0

Released on 05-April-2020

Changes:
* change loading of extension; now done manually in LocalSettings.php
* change bootstrap foundation from version 3 to version 4
* added component card
* removed component icon
* deprecated components label, panel, and well
* add translations via translatewiki

Fixes:
* fixed some typos
* fix component popover header handling

Also see the [migration guide](migration-guide.md) when switching to ~4.0.

### BootstrapComponents 1.2.4

Released on 22-January-2020

Please note, that this is the last version to support Mediawiki core 1.27. It still utilizes Bootstrap 3. Upcoming releases will
use Bootstrap 4 but require MW >= 1.31.

Changes:
* add translations via translatewiki

Fixes:
* fixed some typos
* removed master tests

### BootstrapComponents 1.2.3

Released on 28-Sep-2018

Changes:
* add translations via translatewiki

Fixes:
* fix error in travis install script

### BootstrapComponents 1.2.2

Released on 29-May-2018

Changes:
* add translations via translatewiki

Fixes:
* fix modal popup `<div>` not generated (issue #12)
* fix fatal error in BootstrapComponents\Tests\ExecutionTimeTestListener on mw master

### BootstrapComponents 1.2.1

Released on 22-Feb-2018

Changes:
* add translations via translatewiki

Fixes
* fix copy/paste error in "illegal call fix" from v 1.2.0

### BootstrapComponents 1.2

Released on 20-Feb-2018

Changes:
* add support for colors "primary" and "default" to component alert
* add two more known issues concerning the modal
* add more robust argument and return value handling for lua parse() function
* add issue template
* rename namespace for components from `\BootstrapComponents\Component` to
    `\BootstrapComponents\Component` to comply with naming conventions
* introduce class `ParserFirstCallInit` that handles the hook with the same name

Fixes:
* illegal call to User->loadFromSession() triggered by Extension:CodeMirror

### BootstrapComponents 1.1.1

Released on 06-Feb-2018

Changes:
* tooltips now highlighted through css class `bootstrap-tooltip`

Fixes:
* paragraphs inside various components did not show correctly
* component modal broken when image modals were disabled
* carousel unable to process the same image more than once
* component collapse broken for invalid images as trigger

### BootstrapComponents 1.1

Released on 02-Feb-2018

* adds scribunto support, providing module `mw.bootstrap` with functions
  * `parse` to render components
  * `getSkin` to get the name of the current skin

### BootstrapComponents 1.0

Released on 30-Jan-2018

First working version

Provides
* The following components to be used inside wiki text
  * accordion
  * alert
  * badge
  * button
  * carousel
  * collapse
  * icon
  * jumbotron
  * label
  * modal
  * panel
  * popover
  * tooltip
  * well
* A new gallery mode: _carousel_
* Option to have images in wiki text to be replaced by modals
* Tests
* Installation options: mw default or composer
* Documentation
