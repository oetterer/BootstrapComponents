## The following are known issues
Some components cause problems with other components or "external"
elements.

### Skins that load Bootstrap themselves
Some skins put Bootstrap on the page on their own. For those, this extension does not
request Extension:Bootstrap's `ext.bootstrap.styles`, since that would load Bootstrap's
CSS twice. Chameleon, Medik and Tweeki are treated this way. Every other skin, Vector
and Timeless among them, keeps getting Bootstrap from Extension:Bootstrap.

Bootstrap's JavaScript is handled the same way, per skin. The carousel, modal, popover
and tooltip modules depend on the active skin's own Bootstrap when it provides one, so
Medik gets its `skins.medik.js` instead of Extension:Bootstrap's copy, and Chameleon
shares the single `ext.bootstrap.scripts` it already loads. Tweeki is the exception: it
bundles Bootstrap but exposes no `window.bootstrap` global for those modules' init
scripts to reach, so it still loads Extension:Bootstrap's JavaScript alongside its own.
Making Tweeki clean needs those init scripts to fall back to Bootstrap's jQuery plugin
bridge.

With Medik and Tweeki, matching the Bootstrap versions is up to you. They bundle their
own Bootstrap and declare no dependency on Extension:Bootstrap, so nothing checks that
their Bootstrap matches the one this extension expects. BootstrapComponents 6.x targets
Bootstrap 5.3, so pair it with a Bootstrap 5 release of these skins, and stay on
BootstrapComponents 5.x for their Bootstrap 4 releases. Chameleon is not affected: it
depends on Extension:Bootstrap itself, so Composer keeps the two in step.

This extension cannot read the exact Bootstrap version a skin provides where it decides
what to load, on the server. That version is only visible in the browser, too late to
act on. So a mismatch surfaces as a component that looks or behaves wrong, not as an
error message.

### Modals and popovers
When you put popovers on a page with modals (or image modals),
the modals break.

### Modals and vector
Modals are not fully compatible with the vector skin. Therefore they are
missing the backdrop. Also, you might notice a slight "wobble" in
vector's header every time the modal pops up.

### Modals and definition lists
Some user experience broken html output when trying to use modals as the
term in definition lists. Using the html equivalent causes the same
problem.

### Modal backdrops
Sometimes, not only in vector skin, modal backdrops (the greying out of
the background) tend to stack the wrong way. In these instances the
z-index of the modal and the backdrop are ignored and the backdrop
overlays the modal and everything else. You cannot even close the modal
and have to reload the page.

When this happens with your installation you have to disable backdrops
altogether. Please add the following to your `MediaWiki:Common.css` or
anywhere else where css is processed:
```css
.modal-backdrop {
    display: none;
}
```

### Navbar overlaps modal
Oftentimes when you have problems with the backdrop, your z-index calculation
is off. Then it can also happen, that your Navbar overlaps the modal. In that case
simply "push" the modal further down. For example:
```css
.modal {
    top: 60px;
}
```
