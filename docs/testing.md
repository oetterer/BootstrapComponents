## Testing

This extension provides unit and integration tests that are run by a
[continuous integration platform][travis] but can also be executed using the
`composer phpunit` command from the extension base directory that will
run all tests.

In order to run only a specific test suit, the following commands are
provided for convenience:

* `composer unit` to run all unit tests
* `composer integration` to run all integration tests (which requires an
  active MediaWiki, DB connection)
* `composer parser` to run only the parser tests from the integration
  suite (see prerequisites above)

See [Information on unit testing][mw-testing] if you want to expand the
tests yourself.

[travis]: https://travis-ci.org/oetterer/BootstrapComponents
[mw-testing]: https://www.mediawiki.org/wiki/Manual:PHP_unit_testing
