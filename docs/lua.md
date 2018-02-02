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
-- @return string
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


#### Example
```lua
local tooltip = mw.bootstrap.parse( 'tooltip', 'ambiguous', { text='better explanation' } )
local panel1 = mw.bootstrap.parse(
    'panel',
    'This is the text inside the panel1. It it quite' .. tooltip,
    { color = 'success', footer = 'information at the base', collapsible = true }
)
local panel2 = mw.bootstrap.parse(
    'panel',
    'This is the text inside the panel2. It it quite' .. tooltip,
    { color = 'success', footer = 'information at the base', collapsible = true }
)
local accordion = mw.bootstrap.parse(
    'accordion',
    panel1 .. panel2
)
return accordion
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
[components]: components.md