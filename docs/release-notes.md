## Release Notes

### BootstrapComponents 6.1.1

Released on TBD

Fixes:
* stop logging `Unexpected general module ... in styles queue.` on pages using a modal, carousel, tooltip or popover

### BootstrapComponents 6.1.0

Released on 21-July-2026

Fixes:
* fix dropdown menus, accordions and other click-driven components on Medik and Tweeki
* skins that provide their own Bootstrap (Chameleon, Medik and Tweeki) no longer also load Extension:Bootstrap's copy

Changes:
* `api.php?action=parse` no longer lists the `ext.bootstrap.*` modules; anything rendering parsed content outside a normal page view must load Bootstrap itself

### BootstrapComponents 6.0.1

Released on 20-July-2026

Fixes:
* fix text color on anchor-based buttons and colored cards

Changes:
* add a migration guide for the Bootstrap 4 to 5 upgrade
* split the migration guide into one document per upgrade and link both guides from the README

### BootstrapComponents 6.0.0

Released on 16-July-2026

Breaking changes:
* requires MediaWiki 1.43 or later
* requires PHP 8.1 or later
* requires Bootstrap extension 6.x, which bundles Bootstrap library 5.3

Changes:
* change bootstrap foundation from version 4 to version 5.3
* rewrite component JavaScript from jQuery to the native Bootstrap 5 API
* reimplement the jumbotron using Bootstrap 5 utility classes, since Bootstrap 5 removed the .jumbotron component

See the [migration guide](migration-guide-bs5.md) when upgrading from 5.x.

### BootstrapComponents 5.2.4

Released on 22-May-2026

Fixes:
* fix modal backdrop displaying on top of modal content on Vector, Vector 2022, MonoBook, and Timeless skins on MediaWiki 1.43+

### BootstrapComponents 5.2.3

Released on 21-May-2026

Changes:
* add translations via translatewiki

Fixes:
* fix modal, tooltip, and popover not appearing on MediaWiki 1.43+

### BootstrapComponents 5.2.2

Released on 01-April-2026

Changes:
* Improved compatibility with MediaWiki 1.43+
* System message translation updates from translatewiki.net

Fixes:
* fix TypeError in ImageModal when file does not exist
* fix addModuleStyles deprecation

### BootstrapComponents 5.2.1

Released on 15-July-2024

Fixes:
* compatibility issues with chameleon installer

### BootstrapComponents 5.2.0

Released on 15-July-2024

Changes:
* dropping support of MediaWiki < 1.39
* remove auto-loading of Extension:Bootstrap.
* add translations via translatewiki

Fixes:
* fix unit tests (especially for current master)
* fix PHP 8 deprecation notice indicating missing logic encapsulation
  in class AbstractComponent

Refactoring:
* modified extension namespace to be compatible with MediaWiki
  extension namespace schema


### BootstrapComponents 5.1.2

Released on 12-May-2024

Changes:
* add CSS classes to restore the usual infobox layout
* add more style control to the card component

Fixes:
* Lua issues when using images and links in components. This is
  by using strip tags.
* documentation for accordions and Lua

### BootstrapComponents 5.1.1

Released on 07-September-2023

Changes:
* add translations via translatewiki

Fixes:
* unused variable including deprecated call (thanks @Seb35)
* compatibility problems with vector-2022
* broken tests

### BootstrapComponents 5.1.0

Released on 20-March-2023

Changes:
* component card can now be set to show by default
* new attributes for component card: _header-image_ and
  _footer-image_.

Fixes:
* component accordion collapse not working as expected
* lua calls break when no arguments are supplied to component

### BootstrapComponents 5.0.1

Released on 12-March-2023

Changes:
* switch integration tests from travis to github workflows
* add translations via translatewiki
* allow html in popovers, thx @lyrixn

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
* fix component card's unit tests
* fix modal tests for REL1_31
* fix a destined-to-fail lua test
* remove smw from tests (and consequently deactivating integration tests)

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

Also see the [migration guide](migration-guide-bs4.md) when switching to ~4.0.

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
