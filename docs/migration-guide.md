## Migration Guide

There have been some changes between versions ~1.0 and ~4.0. Foremost is that
the new BootstrapComponents utilizes Twitter Bootstrap4. Therefore, it mirrors
changes made by Bootstrap.

Also, extension loading must now be done manually in your LocalSettings, no
matter whether you installed it via composer or manually.

### Changes in extension loading
BootstrapComponents now has to loaded manually, whether you installed it
via composer or by cloning it from github. Just add the following to
your `LocalSettings.php` file:

```
wfLoadExtension( 'BootstrapComponents' );
```

### Changes in Components
There have been some changes in the components provided by Bootstrap4 and
the ExtensionBootstrap. Some of them unfortunately need your attention.

#### Icon
This is the change with the severest impact: the glyphicon font has been
removed. BootstrapComponents unfortunately cannot provide a suitable
replacement. If you need fancy items, please have a look at
[Extension:FontAwesome][FontAwesome].

#### Label
The Label component has been removed, its functionality is now provided
by the Badge Component. **BootstrapComponents takes care of that** by
having the label component inside your wiki text rendered with
bootstrap4's badge attributes and classes.

In other words, you can use these two elements in your wiki and
they both produce the same output:
```html
{{#bootstrap_label: text | .. }}
{{#bootstrap_badge: text | .. }}
```

However, it is recommended that on new pages or new edits you now only
use the badge component.

#### Panel and Well
Bootstrap4 removes the Panel and the Well and introduces the new
component Card. Since "Bootstrap’s cards provide a flexible
and extensible content container with multiple variants and options."
it can be used to render things to look like Panels and Wells.
Again, **BootstrapComponents takes care of that** by rendering
a Well and a Panel like a Bootstrap4 Card.

Subsequently, these calls all produce the same output:
```html
<bootstrap_card [..]>Content text for the box</bootstrap_card>
<bootstrap_panel [..]>Content text for the box</bootstrap_panel>
<bootstrap_well [..]>Content text for the box</bootstrap_well>
```

This includes panels inside accordions, as well.

Again, it is recommended that on new pages or new edits you now only
use the card component.
