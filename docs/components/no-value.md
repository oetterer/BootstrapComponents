## No Values
A no value is any of the following (case insensitive):
- no
- 0
- false
- off
- disabled
- ignored
- whatever means "no" in your content language

## Why use no values
The problem with the "just be present and I'll react to you" attributes
is, that you cant disable them, once you put them in. In other words,
if you want to make a card collapsible depending on the result of
another parser function, you now can have your parser function return
a no value.

### Example
```html
<!-- this does not work: -->
<bootstrap_card {{#if:{{{1|}}}|collapsible|}}>Content text for the panel</bootstrap_card>

<!-- this does: -->
<bootstrap_card collapsible="{{#if:{{{1|}}}|yes|no}}">Content text for the panel</bootstrap_card>
```
