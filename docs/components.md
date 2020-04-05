## Components

Introducing bootstrap components into the wiki text is the main goal of
this extension. Depending on your configuration, none, some, or all of
the following components are available to be used inside the wiki text:

<dl>
<dt>[Accordion](components/accordion.md)</dt>
<dd>An accordion is a collection of cards, showing only the selected one.</dd>

<dt>[Alert](components/alert.md)</dt>
<dd>Provide contextual feedback messages for typical user actions with the
	handful of available and flexible alert messages.</dd>

<dt>[Badge](components/badge.md)</dt>
<dd>Easily highlight new or unread items by adding a badge component to them.

Replaces bootstrap3's label.</dd>

<dt>[Button](components/button.md)</dt>
<dd>Bootstrap provides different styles of buttons that can link to any
	target.</dd>

<dt>[Card](components/card.md) (new)</dt>
<dd>Cards provide a flexible and extensible content container with multiple
variants and options, replacing bootstrap3's panel and well element.</dd>

<dt>[Carousel](components/carousel.md)</dt>
<dd>The Carousel component is for cycling through images, like a carousel
	(slide show).</dd>

<dt>[Collapse](components/collapse.md)</dt>
<dd>Collapses are useful when you want to hide and show large amount of
	content.</dd>

<dt>[Jumbotron](components/jumbotron.md)</dt>
<dd>A jumbotron indicates a big box for calling extra attention to some
	special content or information.</dd>

<dt>[Modal](components/modal.md)</dt>
<dd>The Modal component is a dialog box/popup window that is displayed on
	top of the current page.
</dd>

<dt>[Popover](components/popover.md)</dt>
<dd>The Popover component produces a pop-up box when the user clicks on
    an element.</dd>

<dt>[Tooltip](components/tooltip.md)</dt>
<dd>Displays a tooltip when hovering over an element.</dd>
</dl>

Just add the appropriate code inside your wiki text. If your wiki is
not configured to use this specific component, you will see the code
on the resulting page. If it is whitelisted you will see either the
component or a feedback message on your wiki page.

Please note that the following components have been removed as of bootstrap4:
* Icon (you can use [Extension:FontAwesome][FontAwesome] )
* Label (functionality implemented by [Badge](components/badge.md))
* Panel (functionality implemented by [Card](components/card.md))
* Well (functionality implemented by [Card](components/card.md)


[FontAwesome]: https://www.mediawiki.org/wiki/Extension:FontAwesome
