## Badge
Easily highlight new or unread items by adding a badge component to them.
They can be best utilized with a numerical _text_, but any string will do
fine.

Note that as of Bootstrap4 this also implements your label component.

### Example usage
```html
{{#bootstrap_badge: text | .. }}
```

### Allowed Attributes
The following attributes can be used inside the parser function:

<dl>
<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>color</dt>
<dd>Sets the color for this component. See bootstrap's
[color documentation](https://getbootstrap.com/docs/4.1/utilities/colors/)
for more information.

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

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>pill</dt>
<dd>Makes the badges more round.

You can also set this attribute to any [_no_ value](no-value.md), in which case
it is ignored.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

### Links
* https://getbootstrap.com/docs/4.1/components/badge/
* https://www.w3schools.com/bootstrap4/bootstrap_badges.asp
