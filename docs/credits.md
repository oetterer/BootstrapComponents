## Credits

### Author & Contributors

The author of the BootstrapComponents extension is Tobias Oetterer.

It is based upon the [Bootstrap extension][ExtensionBootstrap] created
by Stephan Gambke. Also, some parts of its documentation is copied and
used here.

Code snippets used for CI on [Travis] and [Scrutinizer] were taken from
the [SemanticScribunto] software repository.

Integration tests use the `JsonTestCaseScriptRunner` class created by
mwjames for [SemanticMediaWiki] and both json test classes and the
`ReadmeContentsBuilder` are adaptions of his work, also.

Translations have been provided by the members of the [Translatewiki.net
project](https://translatewiki.net).

### Dependencies

The BootstrapComponents extension uses the Bootstrap extension by
Stephan Gambke which is installed automatically during installation via
Composer. This extension utilizes several other libraries and modules.
See its documentation on [mediawiki.org][ExtensionBootstrap] and
[GitHub][GitHub].

For integration tests, this extension relies on classes from the extension
[SemanticMediaWiki]. It is only installed, when conduction CI tests on
[Travis].

### Thanks!

My thanks go to Stephan Gambke for creating the Bootstrap extension, to
Karsten Hoffmeyer for providing the [sandbox][Sandbox] for example pages
and all the important "small stuff", and to JeroenDeDauw and especially
mwjames who both where kind enough to help me getting better in coding for
mediawiki projects.

If I forgot somebody, sorry. Please drop me a note, so I can add them here.


[ExtensionBootstrap]: https://www.mediawiki.org/wiki/Extension:Bootstrap
[GitHub]: https://github.com/cmln/mw-bootstrap
[Travis]: https://travis-ci.org
[Scrutinizer]: https://scrutinizer-ci.com
[SemanticScribunto]: https://github.com/SemanticMediaWiki/SemanticScribunto
[SemanticMediaWiki]: https://github.com/SemanticMediaWiki/SemanticMediaWiki
[Sandbox]: https://sandbox.semantic-mediawiki.org/wiki/BootstrapComponents
