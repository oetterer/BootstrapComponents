## Release Notes

### Bootstrap 1.2

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

### Bootstrap 1.1.1

Released on 06-Feb-2018

Changes:
* tooltips now highlighted through css class `bootstrap-tooltip`

Fixes:
* paragraphs inside various components did not show correctly
* component modal broken when image modals were disabled
* carousel unable to process the same image more than once
* component collapse broken for invalid images as trigger

### Bootstrap 1.1

Released on 02-Feb-2018

* adds scribunto support, providing module `mw.bootstrap` with functions
  * `parse` to render components
  * `getSkin` to get the name of the current skin

### Bootstrap 1.0

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
