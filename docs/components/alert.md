## Alert
Provide contextual feedback messages for typical user actions with the
handful of available and flexible alert messages.

See also:
* [Card](card.md)
* [Jumbotron](jumbotron.md)

### Example usage
```html
<bootstrap_alert [..]>Message text</bootstrap_alert>
```

### Allowed Attributes
The following attributes can be used inside the tag:

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

<dt>dismissible</dt>
<dd>If present or set to any value, the alert will get a dismiss-button.
If you set dismissible to <em>fade</em>, the alert will fade out when dismissed.

You can also set this attribute to any [_no_ value](no-value.md), in which case
it is ignored.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

### Links
* https://getbootstrap.com/docs/4.1/components/alerts/
* https://www.w3schools.com/bootstrap4/bootstrap_alerts.asp
