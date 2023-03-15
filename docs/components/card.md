## Card
A card in bootstrap is a bordered box with some padding around its content.
They provide a flexible and extensible content container with multiple variants
and options, replacing bootstrap3's panel and well element.

Cards are also used inside [accordions](accordion.md) to group elements.

See also:
* [Accordion](accordion.md) uses panels to work
* [Collapse](collapse.md) or [modal](modal.md) (if your looking for
    more collapsible components)

### Example usage
```html
<bootstrap_card [..]>Content text for the panel</bootstrap_card>
```

### Allowed Attributes
The following attributes can be used inside the tag:

<dl>
<dt>active</dt>
<dd>If this card is collapsible (see below), this set it to "show" initially.
This is especially useful inside an accordion.

You can also set this attribute to any [_no_ value](no-value.md), in which
case it is ignored.</dd>

<dt>background</dt>
<dd>Sets the color for this component. See bootstrap's color documentation
(link below) for more information.

Allowed Values are
<ul>
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

<dt>class</dt>
<dd>Adds this string to the class attribute of the component. If you want to
add multiple classes, separate them by a space.</dd>

<dt>collapsible</dt>
<dd>Even when not inside an accordion, a card can be made collapsible with this.

You can also set this attribute to any [_no_ value](no-value.md), in which
case it is ignored.</dd>

<dt>color</dt>
<dd>Because bootstrap4's color schema is rather dominant, for downward compatibility
reasons the _color_ attribute only sets a border color. If you want to get the full
bootstrap4 feeling, use attribute _background_ instead.

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

<dt>footer</dt>
<dd>All you supply here will be inserted into the footer area of the panel.</dd>

<dt>footer-image</dt>
<dd>You can specify an image to placed at the bottom of the card body spanning
the whole card width. If you do not supply the css class "card-img-bottom"
to the image (with <code>class=card-img-bottom</code>), it will be inserted
automatically.</dd>

<dt>header</dt>
<dd>All you supply here will be inserted into the header area of the panel.</dd>

<dt>header-image</dt>
<dd>You can specify an image to placed at the top of the card body spanning
the whole card width. If you do not supply the css class "card-img-top"
to the image (with <code>class=card-img-top</code>), it will be inserted
automatically.</dd>

<dt>id</dt>
<dd>Sets the id of the component to this value. See to it, that it is
unique.</dd>

<dt>style</dt>
<dd>Adds this string to the style attribute of the component. If you want to
add multiple css styles, separate them by a semicolon.</dd>
</dl>

### Links
* https://getbootstrap.com/docs/4.1/components/card/
* https://www.w3schools.com/bootstrap4/bootstrap_cards.asp
* https://getbootstrap.com/docs/4.1/utilities/colors/
