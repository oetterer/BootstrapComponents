## Lua support

When you use the [Scribunto extension][Scribunto], the BootstrapComponents extension provides you with an
easy access to the available components inside your lua script. Also, there is a utility function,
to get the name of the skin in use. The available lua functions are:

* [mw.bootstrap.parse](#parse)
* [mw.bootstrap.getSkin](#getskin)

### Parse
This function parses a bootstrap component, taking input text and arguments from the function parameters:

```lua
-- @param string component the name of the component, you want to parse
-- @param string input     the text inside the tags or - for parser functions - after the :
-- @param table  arguments the arguments of the tag or parser function
--
-- @return string  the strip tag for your bootstrap component
mw.bootstrap.parse( component, input, arguments )
```

#### Parameters
The parse function takes up to three parameters and returns the parsed component as string. The
parameters are:

<dl>
<dt>component (string)</dt>
<dd>This is the name of the component, you wish to parse. E.g. "icon", "panel", etc.</dd>

<dt>input (string)</dt>
<dd>The input string for your component. For tags, this is the part usually put in between the
    opening and closing part of the tag. For parser functions, this is the text right after the double colon.</dd>

<dt>arguments (table)</dt>
<dd>If you want/need to pass additional arguments put them in this table.</dd>
</dl>

#### Return value
The parser function returns a strip tag, which will later be replaced by the preprocessor with the actual
component. There are two ways to access the actual parsed component, if you should be needing the raw html:
1. a hidden argument: you can supply an additional argument `noStrip = true` which will return the un-stripped
    string instead of the strip tag
2. use scribuntos's frame object to access the preprocessor: `mw.getCurrentFrame():preprocess()` to un-strip the
    result of parse.


#### Example
Example 1: building two cards
```lua
local buildCards = function()
    local tooltip = mw.bootstrap.parse( 'tooltip', 'ambiguous', { text='better explanation' } )
    local card1 = mw.bootstrap.parse(
        'card',
        'This is the text inside the card1. It it quite ' .. tooltip,
        { color = 'success', footer = 'information at the base', collapsible = true }
    )
    local card2 = mw.bootstrap.parse(
        'card',
        'This is the text inside the card2. It it quite ' .. tooltip,
        { color = 'success', footer = 'information at the base', collapsible = true }
    )
    return card1 .. card2
end
```

#### Accordion issues
BootstrapComponents utilizes recursive parser calls to do a "nesting detection". This way, the card component
knows when it is inside an accordion, and renders a little bit differently that normal. This approach however,
does not work inside lua since essentially the parser calls are inverted. There is a workaround, and it looks
like this

```lua
local tooltip = mw.bootstrap.parse( 'tooltip', 'ambiguous', { text='better explanation' } )
local inner = [[<bootstrap_card heading="Headline for Card1">Text inside the card</bootstrap_panel>
    <bootstrap_card heading="Headline for Card2">Text inside the card</bootstrap_panel>
    <bootstrap_card heading="Headline for Card3" color="danger" active>Text inside the card]] .. tooltip ..  [[</bootstrap_panel>
    <bootstrap_card heading="Headline for Card4" color="info">Text inside the card</bootstrap_panel>
    <bootstrap_card color="info">Text inside the card</bootstrap_panel>]]
return mw.bootstrap.parse(
    'accordion',
    inner,
    {}
)
```


For information about the existing components and their available arguments, please visit the
[components documentation][components]

### getSkin
This function returns the currently active skin.

```lua
-- no parameters
--
-- @return string (lower case)
mw.bootstrap.getSkin()
```

#### Example
```lua
local skin = mw.bootstrap.getSkin()
if skin == 'vector' then
    --
elseif skin == 'chameleon' then
    --
else
    --
end
```

[Scribunto]: https://www.mediawiki.org/wiki/Extension:Scribunto
[components]: bs3/components.md
