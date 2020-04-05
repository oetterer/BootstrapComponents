## Accordion
An accordion groups collapsible [cards](card.md) together to a single
unit in a way, that opening one card closes all others.

Note that cards inside an accordion are collapsible by default. You
do not have to set that attribute.

See also:
* [Card](card.md)
* [Collapse](collapse.md)

### Example usage
```html
<bootstrap_accordion [..]>
  <bootstrap_card [..]>Content text for the first panel</bootstrap_card>
  <bootstrap_card [..]>Content text for the second panel</bootstrap_card>
  <bootstrap_card [..]>Content text for the third panel</bootstrap_card>
</bootstrap_accordion>
```

### Allowed Attributes
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

### Links
* https://getbootstrap.com/docs/4.1/components/collapse/#accordion-example
* https://www.w3schools.com/bootstrap4/bootstrap_collapse.asp

