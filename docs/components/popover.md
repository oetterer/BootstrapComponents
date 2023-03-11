## Popover
The Popover component is similar to tooltips or collapses; it is a pop-up
box that appears when the user clicks on an element. The difference to
tooltip is that the popover can contain much more content.

See also:
* [Collapse](collapse.md)
* [Tooltip](tooltip.md)

### Example usage
```html
<bootstrap_popover text="" heading="" [..]>Content for the pop up</bootstrap_popover>
```

### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component. See bootstrap's color documentation
(link below) for more information.

Allowed Values are
<ul>
<li>default</li>
<li>primary</li>
<li>secondary</li>
<li>success</li>
<li>danger</li>
<li>warning</li>
<li>info</li>
<li>light</li>
<li>dark</li>
<li>white</li>
</ul></dd>

<dt>header</dt>
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


### Links
* https://getbootstrap.com/docs/4.1/components/popovers/
* https://www.w3schools.com/bootstrap4/bootstrap_popover.asp
* https://getbootstrap.com/docs/4.1/utilities/colors/
