## Jumbotron

> **Note.** Bootstrap 5 removed the `.jumbotron` component. The
> `bootstrap_jumbotron` parser function is retained for backward
> compatibility and now emits the equivalent Bootstrap 5 utility-class
> combination per the official migration guide linked below.

A jumbotron indicates a big box for calling extra attention to some special
content or information.

A jumbotron is displayed as a grey box with rounded corners.

See also:
* [Alert](alert.md)
* [Card](card.md)
* [Modal](modal.md)

### Example usage
```html
<bootstrap_jumbotron [..]>Content of the jumbotron</bootstrap_jumbotron>
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
* https://getbootstrap.com/docs/5.3/migration/#jumbotron
* https://getbootstrap.com/docs/5.3/examples/jumbotron/
