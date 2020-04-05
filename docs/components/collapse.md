## Collapse
Collapses are useful when you want to hide and show large amount of content.

See also:
* [Accordions](accordion.md) consist of multiple collapsible elements
* A [card](card.md) can also be _collapsible_
* [Modals](modal.md) can also be used to hide and show content
* [Popovers](popover.md) have a more tooltip like design

### Example usage
```html
<bootstrap_collapse text="Collapse button text|[[File:TriggerImage.png|..]" [..]>Text inside the collapse</bootstrap_collapse>
```

### Allowed Attributes
This uses all the allowed attributes of the [button](button.md)
and they will be used in the same manner. Exceptions follow:

<dl>
<dt>text</dt>
<dd>This is a <b>mandatory</b> field.

If you supply text, a [button](button.md) will be generated and used
as the trigger for the collapse.

If you supply an image tag, it is stripped of any link tags and then
be used as the trigger element. In this case, all but the attributes
<em>class</em>, <em>style</em>, and <em>id</em> will be ignored.</dd>
</dl>

### Links
* https://getbootstrap.com/docs/4.1/components/collapse/
* https://www.w3schools.com/bootstrap4/bootstrap_collapse.asp
