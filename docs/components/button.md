## Button
Bootstrap provides different styles of buttons that can link to any target.

### Example usage
```html
{{#bootstrap_button: target | .. }}
```

### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>active</dt>
<dd>Makes the button to appear pressed.

You can also set this attribute to any [_no_ value](no-value.md), in which case
it is ignored.</dd>

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

<dt>disabled</dt>
<dd>Disables the button visually and functionally.

You can also set this attribute to any [_no_ value](no-value.md), in which case
it is ignored.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>outline</dt>
<dd>Removes all background color an images from the button, making it less dominant
in appearance

You can also set this attribute to any [_no_ value](no-value.md), in which case
it is ignored.</dd>

<dt>size</dt>
<dd>You can choose a size for your button. Possible options are:
<ul>
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


### Links
* https://getbootstrap.com/docs/4.1/components/buttons/
* https://www.w3schools.com/bootstrap4/bootstrap_buttons.asp
* https://getbootstrap.com/docs/4.1/utilities/colors/
*
