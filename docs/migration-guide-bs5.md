## Migration Guide: Bootstrap 4 to 5

Bootstrap Components 6.0 moves the underlying framework from Bootstrap 4
to Bootstrap 5.3. All components keep their wikitext syntax: no parser
function or tag was removed or renamed, and existing pages keep parsing
as before. What needs attention is everything a wiki builds around the
emitted markup.

### New requirements

* MediaWiki 1.43 or later
* PHP 8.1 or later
* Bootstrap extension 6.x, which bundles the Bootstrap 5.3 library

Upgrade the Bootstrap extension together with Bootstrap Components; the
two versions belong together.

### Jumbotron

Bootstrap 5 removed the `.jumbotron` component.
**Bootstrap Components takes care of that** by rebuilding the component
from [utility classes][BS5-jumbotron] (`p-5 mb-4 bg-body-tertiary
rounded-3`), so existing jumbotrons keep working. Expect slight visual
differences, and note that custom CSS targeting `.jumbotron` no longer
matches anything.

### Custom CSS targeting emitted markup

Bootstrap 5 renamed classes and restructured some components, and the
markup this extension emits changed accordingly. Rules in
`MediaWiki:Common.css` (or skin CSS) written against the old output
need review. The changes most likely to matter:

| Emitted before | Emitted now |
| -------------- | ----------- |
| `close` (alert and modal close button) | `btn-close` |
| `badge-{color}` | `text-bg-{color}` |
| `badge-pill` | `rounded-pill` |
| `jumbotron` | `p-5 mb-4 bg-body-tertiary rounded-3` |
| carousel indicators as `<ol>`/`<li>` | `<button>` elements |

### Hand-written Bootstrap HTML in templates

Bootstrap 5 only reacts to `data-bs-*` attributes. This extension
renamed the attributes it emits, but raw Bootstrap 4 snippets pasted
into templates or pages are the wiki's responsibility and need the same
rename: `data-toggle` becomes `data-bs-toggle`, and likewise for
`data-target`, `data-dismiss`, `data-ride`, `data-slide`,
`data-slide-to`, `data-parent`, `data-placement`, `data-content`, and
`data-trigger`.

### Gadgets and site JavaScript

The extension now initializes its components through the native
Bootstrap 5 API instead of jQuery plugins. Bootstrap 5 still registers
its jQuery plugin interface when jQuery is present (which is always the
case in MediaWiki), so existing gadget code along the lines of
`$( '#myModal' ).modal( 'show' )` generally keeps working. New code
should use the native API, e.g.
`bootstrap.Modal.getOrCreateInstance( element ).show()`.

### Customized SCSS variables

If your wiki customizes Bootstrap through SCSS variable overrides via
the Bootstrap extension, review the variable names: Bootstrap 5 renamed
and removed a number of them. See the [Bootstrap 5 migration
notes][BS5-migration] for the authoritative list.

### After the upgrade

Pages rendered before the upgrade still carry Bootstrap 4 markup in the
parser cache while the wiki already serves Bootstrap 5 CSS and
JavaScript, so components on cached pages can look broken. This
resolves as pages re-render; to force it, purge the affected pages or
run the `purgeParserCache` maintenance script.

[BS5-jumbotron]: https://getbootstrap.com/docs/5.3/examples/jumbotron/
[BS5-migration]: https://getbootstrap.com/docs/5.3/migration/
