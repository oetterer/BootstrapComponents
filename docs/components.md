## Components

Introducing bootstrap components into the wiki text is the main goal of
this extension. Depending on your configuration, none, some, or all of
the following components are available to be used inside the wiki text:

* **[Accordion](components/accordion.md)**: An accordion is a collection
  of cards, showing only the selected one. ()
* **[Alert](components/alert.md)**: Provide contextual feedback messages
  for typical user actions with the handful of available and flexible
  alert messages.
* **[Badge](components/badge.md)**: Easily highlight new or unread items
  by adding a badge component to them. _Replaces bootstrap3's label._
* **[Button](components/button.md)**: Bootstrap provides different styles
  of buttons that can link to any target.
* **[Card](components/card.md)**: Cards provide a flexible and extensible
  content container with multiple variants and options, replacing
  bootstrap3's panel and well element.
* **[Carousel](components/carousel.md)**: The Carousel component is for
  cycling through images, like a carousel (slide show).
* **[Collapse](components/collapse.md)**: Collapses are useful when you
  want to hide and show large amount of content.
* **[Jumbotron](components/jumbotron.md)**: A jumbotron indicates a big
  box for calling extra attention to some special content or information.
* **[Modal](components/modal.md)**: The Modal component is a dialog
  box/popup window that is displayed on top of the current page.
* **[Popover](components/popover.md)**: The Popover component produces a
  pop-up box when the user clicks on an element.
* **[Tooltip](components/tooltip.md)**: Displays a tooltip when hovering
  over an element.


Just add the appropriate code inside your wiki text. If your wiki is
not configured to use this specific component, you will see the code
on the resulting page. If it is whitelisted you will see either the
component or a feedback message on your wiki page.

Please note that the following components have been removed as of
bootstrap4:
* Icon (you can use [Extension:FontAwesome][FontAwesome] )
* Label (functionality implemented by [Badge](components/badge.md))
* Panel (functionality implemented by [Card](components/card.md))
* Well (functionality implemented by [Card](components/card.md)


[FontAwesome]: https://www.mediawiki.org/wiki/Extension:FontAwesome
