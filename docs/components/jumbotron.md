## Jumbotron

> **⚠️ Deprecated.** Bootstrap 5 removed the `.jumbotron` component. The
> `bootstrap_jumbotron` parser function is retained for backward compatibility
> and now emits the equivalent Bootstrap 5 utility-class combination
> (`p-5 mb-4 bg-body-tertiary rounded-3`) per the official migration guide.
> New content should prefer composing utility classes directly. The parser
> function may be removed in a future major release.

A jumbotron indicates a big box for calling extra attention to some special
content or information.

A jumbotron is displayed as a light box with rounded corners. The Bootstrap 4
version also enlarged the font sizes of the text inside it; the Bootstrap 5
utility-class approximation does not — apply `display-*` utility classes to the
contained headings if you want the BS4-era larger-font look.

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
* https://www.w3schools.com/bootstrap4/bootstrap_jumbotron.asp
