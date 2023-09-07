# Bootstrap Components
[![Build Status](https://github.com/oetterer/BootstrapComponents/actions/workflows/ci.yml/badge.svg)](https://github.com/oetterer/BootstrapComponents/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/mediawiki/bootstrap-components/version.png)](https://packagist.org/packages/mediawiki/bootstrap-components)
[![Packagist download count](https://poser.pugx.org/mediawiki/bootstrap-components/d/total.png)](https://packagist.org/packages/mediawiki/bootstrap-components)
[![Code Coverage](https://scrutinizer-ci.com/g/oetterer/BootstrapComponents/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/oetterer/BootstrapComponents/?branch=master)
[![Code Quality](https://scrutinizer-ci.com/g/oetterer/BootstrapComponents/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/oetterer/BootstrapComponents/?branch=master)

Bootstrap Components is a [MediaWiki] extension that aims to provide
editors with an easy access to certain components introduced by
[Twitter Bootstrap 4][Bootstrap].

Depending on your configuration, editors can utilize a number of
_tag extensions_ and _parser functions_ inside wiki code to place certain
bootstrap components on MediaWiki pages. Also, depending on your
configuration it can add a new [gallery][Gallery] mode, and replace normal
[image rendering][Image] with an image modal.

## Requirements
* PHP 7.4 or later
* MediaWiki 1.35 or later

## Documentation
- [Installation and configuration](docs/installation-configuration.md)
- [Usage](docs/components.md)
- [Lua support](docs/lua.md)
- [Release notes](docs/release-notes.md)
- [Testing](docs/testing.md)
- [Contributing](docs/contributing.md)
- [Credits](docs/credits.md)
- [License](docs/licensing.md)

Please also see the [known issues][known-issues] section.

There is also a [migration guide](docs/migration-guide.md) for users switching
from bootstrap3 (BootstrapComponents ~1.2) to bootstrap4 (BootstrapComponents ~4.0).

## Contact
For bug reports and feature requests please see, if it is already reported on
the list of [open bugs][open bugs]. If not, [report it][report bugs]. Also see the
[contribute](docs/contributing.md) section for instructions on bug reporting and
the list of [known issues][known-issues].

For general questions, comments, or suggestions you might use the [talk page
on MediaWiki.org][mw-talk]. For direct contact with the author
please use the [Email functionality on MediaWiki.org.][mw-mail]

## Bootstrap3
If you are using bootstrap3, please use the [legacy documentation](docs/bs3/README.md).


[MediaWiki]: https://www.mediawiki.org/
[Bootstrap]: http://getbootstrap.com/
[Gallery]: https://www.mediawiki.org/wiki/Help:Images#Rendering_a_gallery_of_images
[Image]: https://www.mediawiki.org/wiki/Help:Images#Rendering_a_single_image
[known-issues]: docs/known-issues.md
[open bugs]: https://github.com/oetterer/BootstrapComponents/issues
[report bugs]: https://github.com/oetterer/BootstrapComponents/issues/new
[mw-talk]: https://www.mediawiki.org/wiki/Extension_talk:BootstrapComponents
[mw-mail]: https://www.mediawiki.org/wiki/Special:EmailUser/oetterer
