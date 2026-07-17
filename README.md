# Bootstrap Components
[![Build Status](https://github.com/oetterer/BootstrapComponents/actions/workflows/ci.yml/badge.svg)](https://github.com/oetterer/BootstrapComponents/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/mediawiki/bootstrap-components.svg)](https://packagist.org/packages/mediawiki/bootstrap-components)
[![Total Download Count](https://img.shields.io/packagist/dt/mediawiki/bootstrap-components.svg)](https://packagist.org/packages/mediawiki/bootstrap-components/stats)

Bootstrap Components is a [MediaWiki] extension that aims to provide
editors with easy access to certain components introduced by
[Bootstrap 5][Bootstrap].

Depending on your configuration, editors can utilize several
_tag extensions_ and _parser functions_ inside wiki code to place certain
bootstrap components on MediaWiki pages. Also, depending on your
configuration it can add a new [gallery][Gallery] mode, and replace normal
[image rendering][Image] with an image modal.

## Requirements
* PHP 8.1 or later
* MediaWiki 1.43 or later

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

There are also migration guides for users switching
to [Bootstrap 5](docs/migration-guide-bs5.md) (Bootstrap Components ~6.0) and
to [Bootstrap 4](docs/migration-guide-bs4.md) (Bootstrap Components ~4.0 and ~5.0).

## Contact
For bug reports and feature requests, please see if it is already reported on
the list of [open bugs][open bugs]. If not, [report it][report bugs]. Also, see the
[contribute](docs/contributing.md) section for instructions on bug reporting and
the list of [known issues][known-issues].

Use the [talk page on MediaWiki.org][mw-talk] for general questions, comments,
or suggestions.

[MediaWiki]: https://www.mediawiki.org/
[Bootstrap]: http://getbootstrap.com/
[Gallery]: https://www.mediawiki.org/wiki/Help:Images#Rendering_a_gallery_of_images
[Image]: https://www.mediawiki.org/wiki/Help:Images#Rendering_a_single_image
[known-issues]: docs/known-issues.md
[open bugs]: https://github.com/oetterer/BootstrapComponents/issues
[report bugs]: https://github.com/oetterer/BootstrapComponents/issues/new
[mw-talk]: https://www.mediawiki.org/wiki/Extension_talk:BootstrapComponents
