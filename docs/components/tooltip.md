## Tooltip
Displays a tooltip when hovering over an element.

See also:
* [Popover](popover.md)

### Example usage
```html
{{#bootstrap_tooltip: content of the tooltip | text= | .. }}
```

### Allowed Attributes
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

### Note
Tooltips are marked with a dotted underline. If you want to disable this, add the
following to your `Mediawiki:Common.css`:
```css
.bootstrap-tooltip {
    border-bottom: none;
}
```

### Links
* https://getbootstrap.com/docs/4.1/components/tooltips/
* https://www.w3schools.com/bootstrap4/bootstrap_tooltip.asp
