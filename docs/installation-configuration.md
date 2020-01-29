## Installation
There are two ways you can install this extension: you can use [Composer]
for installation, activation, and dependency management or you can do this
all manually.

### Composer
This is the easiest and recommended approach. Just add the following to
the "require"-section of your `composer.local.json` file and run the
`php composer.phar install/update "mediawiki/bootstrap-components"` command.

```
{
	"require": {
		"mediawiki/bootstrap-components": "~1.0"
	}
}
```

### Manually
You need to download and activate the extension yourself. Also, you need to
take care of dependencies.

First, you should take care of dependencies. That means installing
the MediaWiki [Extension Bootstrap][BootstrapExtension]. See there
for details on how to do this.

Then you need to download this extension, by cloning its repository
using [git][Git]. Venture into your extensions directory and run:

```
git clone https://github.com/oetterer/BootstrapComponents
```

You can also download the [archive][GitArchive] and extract it yourself.

Note that getting the extension manually from GitHub leaves you with the
must current version of the extension.

## Loading

You need to add the following to your `LocalSettings.php` file, no matter
whether you installed the extension via composer or manually:

```
wfLoadExtension( 'BootstrapComponents' );
```

Check your wiki's Version special page to verify, that the extension is loaded.

## Configuration
You can change some of the behaviour of this extension with the
following settings inside your wiki's configuration. Just add the
corresponding line to your `LocalSettings.php`.

Available settings:
* [$wgBootstrapComponentsWhitelist](#wgbootstrapcomponentswhitelist)
* [$wgBootstrapComponentsModalReplaceImageTag](#wgbootstrapcomponentsmodalreplaceimagetag)
* [$wgBootstrapComponentsDisableSourceLinkOnImageModal](#wgbootstrapcomponentsdisablesourcelinkonimagemodal)
* [$wgBootstrapComponentsEnableCarouselGalleryMode](#wgbootstrapcomponentsenablecarouselgallerymode)

### `$wgBootstrapComponentsWhitelist`
Default setting is `true`.

This allows you to enable all, some, or none of the components inside
your wiki code.

If you want all components available, set this to `true`:
```
$wgBootstrapComponentsWhitelist = true;
```

If you want only a selection of components, set this to an array
containing the whitelisted components. For a list of all components,
please refer to [Components].
```
$wgBootstrapComponentsWhitelist = [ 'icon', 'panel', 'tooltip', 'modal' ];
```

When using modals, you might want to disable popovers. See
[known issues][known-issues] for more.

To disable all components simply set this to `false`.

### `$wgBootstrapComponentsModalReplaceImageTag`
Default setting is `false`.

You can have this extension change the normal image handling in your
wiki. If you set this to `true`, all image tags not containing a `link=`
parameter will be converted into a modal. So when you click on the image,
instead of being referred to the corresponding page in the file namespace,
a modal opens up showing the image with a possible caption (if you
provided one) and a link to the source page of the file.

Example:
```
$wgBootstrapComponentsModalReplaceImageTag = true;
```

Most regular options that can be used with images are recognized and
processed accordingly. Please see the help page on [mediawiki.org][ImageHelp].

Even after enabling this option you can suppress image modals on a
given page by adding the magic word `__NOIMAGEMODAL__` somewhere on
the page. If you want to suppress modal conversion for an individual image,
give it the css class `no-modal`.

Example:
```
[[File:NormalImage.png|class=no-modal|alt=normal|This image is displayed normally]]
```

Please see [known issues][known-issues] for additional information.

### `$wgBootstrapComponentsDisableSourceLinkOnImageModal`
Default setting is `false`.

When using image modals (thus having
`$wgBootstrapComponentsModalReplaceImageTag` set to true) enabling
this suppresses the source link in the footer section of the modal.

### `$wgBootstrapComponentsEnableCarouselGalleryMode`
Default setting is `true`.

This adds the mode _carousel_ to the `<gallery>`-tag which, when used
turns your gallery into a carousel. For Information on how to use
galleries, please visit [mediawiki.org][Gallery].

[Composer]: https://getcomposer.org/
[Git]: https://git-scm.com/
[GitArchive]: https://github.com/oetterer/BootstrapComponents/archive/master.zip
[BootstrapExtension]: https://www.mediawiki.org/wiki/Extension:Bootstrap
[Components]: components.md
[known-issues]: known-issues.md
[ImageHelp]: https://www.mediawiki.org/wiki/Help:Images
[Gallery]: https://www.mediawiki.org/wiki/Help:Images#Rendering_a_gallery_of_images
