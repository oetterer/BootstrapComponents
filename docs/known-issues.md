## The following are known issues
Some components cause problems with other components or "external"
elements.

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
