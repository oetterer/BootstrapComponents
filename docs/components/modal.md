## Modal
The Modal component is a dialog box/popup window that is displayed on top
of the current page.

Please note that it is not 100% compatible with the vector
skin. You might be able to notice a slight "wobble" when activating the
modal.

See also:
* [Accordions](accordion.md) consist of multiple collapsible elements
* A [Jumbotron](jumbotron.md) can also be used to emphasize content
* [Cards](card.md), or [collapses](collapse.md) are another way to
    show/hide content.

### Example usage
```html
<bootstrap_modal text="" [..]>Content of the modal</bootstrap_modal>
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

<dt>footer</dt>
<dd>All you supply here will be inserted into the footer area of the modal.</dd>

<dt>header</dt>
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

If you supply text, a [button](button.md) will be generated and used
as the trigger for the modal.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element.</dd>
</dl>

### Links
* https://getbootstrap.com/docs/4.1/components/modal/
* https://www.w3schools.com/bootstrap4/bootstrap_modal.asp
* https://getbootstrap.com/docs/4.1/utilities/colors/

Please, see also the [known issues](../known-issues.md) with this component.
