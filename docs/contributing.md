## How to contribute

There are different ways to make a contribution to this extension. A few guidelines
are provided here to keep the workflow and review process most efficient.

### Report bugs, ask for features

You may help by reporting bugs and feature requests. Before you do,
please make sure you run the latest version of BootstrapComponents
and you are not about to address a [known-issue][known-issues]. Then
check if a corresponding open bug already exists on the list of
[open bugs][open bugs] and if you have new information, comment on it.
If the bug is not yet reported, [open a new bug report][report bugs].

When you report a bug, please include:
* Exact steps to reproduce the bug
* Expected result
* Observed result
* Versions of PHP, MediaWiki, BootstrapComponents, Browsers, other relevant
    software (web server, MediaWiki extensions)
* Other information that may be relevant
* If available a web link, where this bug can be seen

If in doubt, don't worry. You will be asked for what is missing.

MediaWiki has some more advice on [how to report a bug][how to report a bug].

### Improve the documentation

* You would really help by creating, updating or amending the documentation of
  the components in the `/docs` folder. Although the documentation is the main
  source of information for anybody who would want to use the extension it
  never gets the attention it deserves. (Stephan, you are so on point)
* You may provide a [screenshot][screenshots] of the component used on
  your wiki and add it to its documentation.
* Finally, you may help by providing translations via [translatewiki.net][twn].
  See their [progress statistics][twn-stats] to find out if there is still work
  to do for your language.

### Provide patches

The BootstrapComponents extension is hosted on GitHub. To provide patches
you need to get an account.

A few points to ease the process:
* Please ensure that patches are based on the current master.
* Code should be easily readable and if necessary be put into separate
  components (or classes). Also, please follow the [MediaWiki coding
  conventions][coding].
* Newly added features should not alter existing tests but instead provide
  additional test coverage to verify the expected new behaviour. For a
  description on how to write and run PHPUnit test, please consult the
  [manual][mw-testing].
* If you want to add new components, follow the guidelines under
  [Adding components](#adding-components).

### Adding components
1. Every component gets its own class. Implement child of class
   [`AbstractComponent`][ClassAbstractComponent]. Files for component
   classes are located in the `src/Component` directory.
2. Add your class to the autoloader section of [extension.json]
3. In class [`ComponentLibrary`][classComponentLibrary], look for the
   method `rawComponentsDefinition()` and add all necessary data
   for the new component
4. Create all necessary message entries in [qqq.json] and [en.json].
5. Document the new component in [components.md]
6. Add tests for your new class. For guidelines on unit testing see
   information on [mediawiki.org](mw-testing)
7. Adjust existing tests / provider:
  * `AbstractComponentTest::allComponentsProvider()`
  * `ComponentLibraryTest::testCanCompileMagicWordsArray`
  * `ComponentLibraryTest::compileParserHookStringProvider`
  * `ComponentLibraryTest::componentNameAndClassProvider`
  * `ComponentLibraryTest::modulesForComponentsProvider`
  * `SetupTest::testHookParserFirstCallInit`

#### Format of data array in ComponentLibrary::rawComponentsDefinition
```
    [
        (string)[component name, lower case] => [
            'class' => (string)'\\BootstrapComponents\\Components\\[Component Class]',
            'handlerType' => [self::HANDLER_TYPE_PARSER_FUNCTION or self::HANDLER_TYPE_TAG_EXTENSION],
            'attributes' => [
                'default' => (bool)true|false [does this component allow the default attributes]
                (string)... [list of individual attributes, must be registered in AttributeManager]
            ],
            'modules' => [
                'default' => (string|array)[modules to load when this component is parsed]
                '[skin name]' => (string|array)[modules to load when this component is parsed and [skin name] is active]
            ]
        ],
    ];
```
#### Abstract method to implement in new class
```
public function placeMe( $input ) {
    ...
    return <(array|string) your component html code>
    // see also https://www.mediawiki.org/wiki/Manual:Parser_functions#Controlling_the_parsing_of_output
}
```

[known-issues]: known-issues.md
[open bugs]: https://github.com/oetterer/BootstrapComponents/issues
[report bugs]: https://github.com/oetterer/BootstrapComponents/issues/new
[how to report a bug]: https://www.mediawiki.org/wiki/How_to_report_a_bug
[screenshots]: https://www.mediawiki.org/wiki/Extension:BootstrapComponents#Screenshots
[twn]: https://translatewiki.net/
[twn-stats]: https://translatewiki.net/w/i.php?title=Special%3AMessageGroupStats&x=D&group=mwgithub-bootstrapcomponents&suppressempty=1
[coding]: https://www.mediawiki.org/wiki/Manual:Coding_conventions
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
[ClassAbstractComponent]: ../src/AbstractComponent.php
[extension.json]: ../extension.json
[classComponentLibrary]: ../src/ComponentLibrary.php
[qqq.json]: ../i18n/qqq.json
[en.json]: ../i18n/en.json
[components.md]: bs3/components.md
