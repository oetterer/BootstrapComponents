## Migration Guide

### Migrating from Bootstrap 4 (BootstrapComponents 5.x) to Bootstrap 5 (BootstrapComponents 6.0)

BootstrapComponents 6.0 upgrades the underlying Bootstrap framework from version 4 to version 5.3. This guide helps you understand the changes and migrate your wiki.

#### System Requirements
- **MediaWiki:** 1.43 or later (upgraded from 1.39)
- **PHP:** 8.1 or later (upgraded from 8.0)
- **Bootstrap Extension:** mediawiki/bootstrap ^6.0 from [ProfessionalWiki/Bootstrap](https://github.com/ProfessionalWiki/Bootstrap)

#### User Impact
**Good News:** Most existing wiki markup using BootstrapComponents should continue to work without any changes! The extension handles most Bootstrap 5 migrations internally.

#### Component Changes

##### Jumbotron
The Jumbotron component still works but now uses Bootstrap 5 utility classes instead of the removed `.jumbotron` class. The visual appearance should be similar but may have minor differences. Consider:
- Using utility classes directly: `<div class="p-5 mb-4 bg-body-tertiary rounded-3">`
- Or continue using `<bootstrap_jumbotron>` tag which is now implemented using these utilities
- Reference: https://getbootstrap.com/docs/5.3/examples/jumbotron/

##### Button Colors
The `color="default"` attribute is automatically mapped to `color="secondary"` in Bootstrap 5. If you prefer different colors:
- Use `color="light"` for light gray buttons
- Use `color="secondary"` explicitly for the standard secondary color

##### Badge Pill
If you're using custom CSS targeting `.badge-pill`, update to `.rounded-pill`:
```css
/* Old */
.badge-pill { ... }

/* New */
.rounded-pill { ... }
```

##### Alert and Modal Close Buttons
Close buttons now use Bootstrap 5's `.btn-close` class. If you have custom CSS targeting `.close`:
```css
/* Old */
.close { ... }

/* New */
.btn-close { ... }
```

#### JavaScript Changes
Bootstrap 5 removed jQuery dependency. If you have custom JavaScript interacting with Bootstrap components:

**Old (Bootstrap 4 + jQuery):**
```javascript
$('.carousel').carousel();
$('[data-toggle="tooltip"]').tooltip();
```

**New (Bootstrap 5 vanilla JS):**
```javascript
document.querySelectorAll('.carousel').forEach(el => {
    new bootstrap.Carousel(el);
});
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el);
});
```

#### Data Attributes
If you're using custom HTML with Bootstrap data attributes, update to the `data-bs-*` prefix:
- `data-toggle` → `data-bs-toggle`
- `data-target` → `data-bs-target`
- `data-dismiss` → `data-bs-dismiss`
- `data-slide` → `data-bs-slide`
- `data-parent` → `data-bs-parent`
- `data-content` → `data-bs-content`
- `data-placement` → `data-bs-placement`
- `data-trigger` → `data-bs-trigger`

#### Testing Your Upgrade
After upgrading:
1. Test all pages using BootstrapComponents
2. Verify modals, tooltips, popovers, and carousels work correctly
3. Check custom CSS for compatibility
4. Test any custom JavaScript interacting with Bootstrap
5. Verify with different MediaWiki skins (Vector, Vector-2022)

#### Rollback
If you encounter issues, you can rollback to BootstrapComponents 5.x which uses Bootstrap 4.

---

### Migrating from Bootstrap 3 (BootstrapComponents 1.x) to Bootstrap 4 (BootstrapComponents 4.x-5.x)

There have been some changes between versions ~1.0 and ~4.0. Foremost is that
the new BootstrapComponents utilizes Twitter Bootstrap 4. Therefore, it mirrors
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
