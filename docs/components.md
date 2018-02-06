## Components

Introducing bootstrap components into the wiki text is the main goal of
this extension. Depending on your configuration, none, some, or all of
the following components are available to be used inside the wiki text:

* [Accordion](#accordion)
* [Alert](#alert)
* [Badge](#badge)
* [Button](#button)
* [Carousel](#carousel)
* [Collapse](#icon)
* [Icon](#jumbotron)
* [Jumbotron](#jumbotron)
* [Label](#label)
* [Modal](#modal)
* [Panel](#panel)
* [Popover](#popover)
* [Tooltip](#tooltip)
* [Well](#well)

Just add the appropriate code inside your wiki text. If your wiki is
not configured to use this specific component, you will see the code
on the resulting page. If it is whitelisted you will see either the
component or a feedback message on your wiki page.

-------------------------------------------------------------------------
### Accordion
An accordion groups collapsible [panels](#panel) together to a single
unit in a way, that opening one panel closes all others.

Note that panels inside an accordion are collapsible by default. You
do not have to set that attribute.

See also:
* [Collapse](#collapse)
* [Panel](#panel)

#### Example usage
```html
<bootstrap_accordion [..]>
  <bootstrap_panel [..]>Content text for the first panel</bootstrap_panel>
  <bootstrap_panel [..]>Content text for the second panel</bootstrap_panel>
  <bootstrap_panel [..]>Content text for the third panel</bootstrap_panel>
</bootstrap_accordion>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_collapse.asp

-------------------------------------------------------------------------
### Alert
Provide contextual feedback messages for typical user actions with the
handful of available and flexible alert messages.

See also:
* [Well](#well)

#### Example usage
```html
<bootstrap_alert [..]>Message text</bootstrap_alert>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>dismissible</dt>
<dd>If present or set to any value, the alert will get a dismiss-button.
If you set dismissible to <em>fade</em>, the alert will fade out when dismissed.

You can also set this attribute to any [_no_ value](#no-values), in which case
it is ignored [(?)](#why-use-no-values).</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_alerts.asp
* https://getbootstrap.com/docs/3.3/components/#alerts

-------------------------------------------------------------------------
### Badge
Easily highlight new or unread items by adding a badge component to them.
They can be best utilized with a numerical _text_, but any string will do
fine.

See also:
* [Label](#label)

#### Example usage
```html
{{#bootstrap_badge: text | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_badges_labels.asp
* https://getbootstrap.com/docs/3.3/components/#badges

-------------------------------------------------------------------------
### Button
Bootstrap provides different styles of buttons that can link to any target.

#### Example usage
```html
{{#bootstrap_button: target | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>active</dt>
<dd>Having this attribute simply present or set to a non-[_no value_](#no-values)
makes a button appear pressed.

You can also set this attribute to any <em>no value</em>, in which case
it is ignored [(?)](#why-use-no-values).</dd>

<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>disabled</dt>
<dd>Having this attribute simply present or set to a non-[_no value_](#no-values)
disables the button.

You can also set this attribute to any <em>no value</em>, in which case
it is ignored [(?)](#why-use-no-values).</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>size</dt>
<dd>You can choose a size for your button. Possible options are:
<ul>
<li>xs</li>
<li>sm</li>
<li>md (default)</li>
<li>lg</li>
</ul></dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>

<dt>text</dt>
<dd>This text will be displayed on the button. If omitted, the target is
used.

If you supply an image tag, it is stripped of any link tags and then
be used inside the button. Best use a transparent image or match image
background with button color.</dd>
</dl>


#### Links
* https://www.w3schools.com/bootstrap/bootstrap_buttons.asp

-------------------------------------------------------------------------
### Carousel
The Carousel component is for cycling through elements, like a carousel (slide show).

#### Example usage
```html
{{#bootstrap_carousel: [[File:Image1|..]] | [[File:Image2|..]] | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Note
If you want to add the same image more than once, you have to "fake" different
attributes, otherwise the parser will drop all but one:

```html
{{#bootstrap_carousel: [[File:Image1]] | 1=[[File:Image1]] | 2=[[File:Image1]] | 3=[[File:Image1]] }}
```

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_carousel.asp

-------------------------------------------------------------------------
### Collapse
Collapses are useful when you want to hide and show large amount of content.

See also:
* [Accordions](#accordion) consist of multiple collapsible elements
* A [panel](#panel) can also be _collapsible_.
* [Modals](#modal) can also be used to hide and show content.

#### Example usage
```html
<bootstrap_collapse text="Collapse button text|[[File:TriggerImage.png|..]" [..]>Text inside the collapse</bootstrap_collapse>
```

#### Allowed Attributes
This uses all the allowed attributes of the [button](#button)
and they will be used in the same manner. Exceptions follow:

<dl>
<dt>text</dt>
<dd>This is a <b>mandatory</b> field.

If you supply text, a [button](#button) will be generated and used
as the trigger for the collapse.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element. In this case, all but the attributes
<em>class</em>, <em>style</em>, and <em>id</em> will be ignored.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_collapse.asp

-------------------------------------------------------------------------
### Icon
Insert the glyph-icon identified by the icon name you provided. See
[online](https://getbootstrap.com/docs/3.3/components/#glyphicons) for
a list of available names.

The name is the string after the "glyphicon glyphicon-"-part. See example.

#### Example usage
```html
{{#bootstrap_icon: icon-name}}
<!-- inserting an asterisk -->
{{#bootstrap_icon: asterisk}}
```

#### Allowed Attributes
None.

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_glyphicons.asp
* https://getbootstrap.com/docs/3.3/components/#glyphicons

-------------------------------------------------------------------------
### Jumbotron
A jumbotron indicates a big box for calling extra attention to some special
content or information.

A jumbotron is displayed as a grey box with rounded corners. It also enlarges
the font sizes of the text inside it.

See also:
* [Modal](#modal)
* [Well](#well)

#### Example usage
```html
<bootstrap_jumbotron [..]>Content of the jumbotron</bootstrap_jumbotron>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_jumbotron_header.asp
* https://getbootstrap.com/docs/3.3/components/#jumbotron

-------------------------------------------------------------------------
### Label
Labels are used to provide additional information about something.

See also:
* [Badge](#badge)

#### Example usage
```html
{{#bootstrap_label: label text | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_badges_labels.asp
* https://getbootstrap.com/docs/3.3/components/#labels

-------------------------------------------------------------------------
### Modal
The Modal component is a dialog box/popup window that is displayed on top
of the current page. Note that it is not 100% compatible with the vector
skin. You might be able to notice a slight "wobble" when activating the
modal.

See also:
*  consist of multiple collapsible elements
* [Jumbotron](#jumbotron) can also be used to emphasize content
* [Accordions](#accordion), [panels](#panel), or [collapses](#collapse)
    are another way to show/hide content.

#### Example usage
```html
<bootstrap_modal text="" [..]>Content of the modal</bootstrap_modal>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>footer</dt>
<dd>All you supply here will be inserted into the footer area of the modal.</dd>

<dt>heading</dt>
<dd>All you supply here will be inserted into the header area of the modal.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>size</dt>
<dd>You can choose a size for your modal. Possible options are:
<ul>
<li>sm</li>
<li>md (default)</li>
<li>lg</li>
</ul></dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>

<dt>text</dt>
<dd>This is a <b>mandatory</b> field.

If you supply text, a [button](#button) will be generated and used
as the trigger for the collapse.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_modal.asp

-------------------------------------------------------------------------
### Panel
A panel in bootstrap is a bordered box with some padding around its content.

See also:
* [Accordion](#accordion) uses panels to work
* [Collapse](#collapse) or [modal](#panel) (if your looking for
    more collapsible components)

#### Example usage
```html
<bootstrap_panel [..]>Content text for the panel</bootstrap_panel>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>active</dt>
<dd>When uses inside an [accordion](#accordion), having this attribute
simply present or set to a non-[_no value_](#no-values) expands this
panel.

You can also set this attribute to any <em>no value</em>, in which case
it is ignored [(?)](#why-use-no-values).</dd>

<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>collapsible</dt>
<dd>Even when not inside an accordion, a panel can be made collapsible. Simply
having this attribute present or set to a non-[_no value_](#no-values)
accomplishes this.

You can also set this attribute to any <em>no value</em>, in which case
it is ignored [(?)](#why-use-no-values).</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>footer</dt>
<dd>All you supply here will be inserted into the footer area of the panel.</dd>

<dt>heading</dt>
<dd>All you supply here will be inserted into the header area of the panel.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_panels.asp
* https://getbootstrap.com/docs/3.3/components/#panels

-------------------------------------------------------------------------
### Popover
The Popover component is similar to tooltips or collapses; it is a pop-up
box that appears when the user clicks on an element. The difference to
tooltip is that the popover can contain much more content.

See also:
* [Tooltip](#tooltip)
* [Collapse](#collapse)

#### Example usage
```html
<bootstrap_popover text="" heading="" [..]>Content for the pop up</bootstrap_popover>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>success</li>
<li>info</li>
<li>warning</li>
<li>danger</li>
</ul></dd>

<dt>heading</dt>
<dd>This is a <b>mandatory</b> field.

This will be inserted into the header area of the popover.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>placement</dt>
<dd>By default, the popover will appear on the right side of the trigger
element. With this, you can place it somewhere else:
<ul>
<li>top</li>
<li>left</li>
<li>bottom</li>
<li>right (default)</li>
</ul></dd>

<dt>size</dt>
<dd>You can choose a size for your trigger button. Possible options are:
<ul>
<li>xs</li>
<li>sm</li>
<li>md (default)</li>
<li>lg</li>
</ul></dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>

<dt>text</dt>
<dd>This is a <b>mandatory</b> field.

This will be used as the text for the popover button.

If you supply an image tag, it is stripped of any link tags and then
be used inside the button. Best use a transparent image or match image
background with button color.</dd>

<dt>trigger</dt>
<dd>By default, the popover is opened when you click on the trigger element,
and closes when you click on the element again. You can change his
behaviour with:
<ul>
<li>default</li>
<li>focus: the popup is closed, when you click somewhere outside the
    element.</li>
<li>hover: the popover is displayed as long as the mouse pointer hovers
    over the trigger element.</li>
</ul></dd>
</dl>


#### Links
* https://www.w3schools.com/bootstrap/bootstrap_popover.asp

-------------------------------------------------------------------------
### Tooltip
Displays a tooltip when hovering over an element.

See also:
* [Popover](#popover)

#### Example usage
```html
{{#bootstrap_tooltip: content of the tooltip | text= | .. }}
```

#### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>placement</dt>
<dd>By default, the popover will appear on top of the element. With this,
you can place it somewhere else:
<ul>
<li>top (default)</li>
<li>left</li>
<li>bottom</li>
<li>right</li>
</ul></dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>

<dt>text</dt>
<dd>This is a <b>mandatory</b> field.

This will be used as the element, the tooltip will be displayed for.</dd>
</dl>

#### Note
Tooltips are marked with a dotted underline. If you want to disable this, add the
following to your `Mediawiki:Common.css`:
```css
.bootstrap-tooltip {
    border-bottom: none;
}
```

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_tooltip.asp

-------------------------------------------------------------------------
### Well
The well component adds a rounded border around content with a gray background
color and some padding.

See also:
* [Alert](#alert)
* [Jumbotron](#jumbotron)

#### Example usage
```html
<bootstrap_well [..]>Message text</bootstrap_well>
```

#### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>size</dt>
<dd>You can choose a size for your well. Possible options are:
<ul>
<li>sm</li>
<li>md (default)</li>
<li>lg</li>
</ul></dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

#### Links
* https://www.w3schools.com/bootstrap/bootstrap_wells.asp
* https://getbootstrap.com/docs/3.3/components/#wells

-------------------------------------------------------------------------

## Addendum

### No Values
A no value is any of the following (case sensitive):
- no
- 0
- false
- off
- disabled
- ignored
- whatever means "no" in your content language

### Why use no values
The problem with the "just be present and I'll react to you" attributes
is, that you cant disable them, once you put them in. In other words,
if you want to make a panel collapsible depending on the result of
another parser function, you now can have your parser function return
a no value.

#### Example
```html
<!-- this does not work: -->
<bootstrap_panel {{#if:{{{1|}}}|collapsible|}}>Content text for the panel</bootstrap_panel>

<!-- this does: -->
<bootstrap_panel collapsible="{{#if:{{{1|}}}|yes|no}}">Content text for the panel</bootstrap_panel>
```