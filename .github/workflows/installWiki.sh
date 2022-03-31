#! /bin/bash

MW_BRANCH=$1

wget https://github.com/wikimedia/mediawiki/archive/$MW_BRANCH.tar.gz -nv

tar -zxf $MW_BRANCH.tar.gz
mv mediawiki-$MW_BRANCH mediawiki

cd mediawiki

composer install
php maintenance/install.php --dbtype sqlite --dbuser root --dbname mw --dbpath $(pwd) --pass AdminPassword WikiName AdminUser

cat <<EOT >> composer.local.json
{
	"require": {
		"mediawiki/semantic-media-wiki": "~4.0"
	},
	"extra": {
		"merge-plugin": {
			"merge-dev": true,
			"include": [
				"extensions/BootstrapComponents/composer.json"
			]
		}
	}
}
EOT

echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
echo 'ini_set("display_errors", 1);' >> LocalSettings.php
echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
echo '$wgShowDBErrorBacktrace = true;' >> LocalSettings.php
echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php

echo 'wfLoadExtension( "Bootstrap" );' >> LocalSettings.php
echo 'wfLoadExtension( "Scribunto" );' >> LocalSettings.php
echo 'wfLoadExtension( "SemanticMediaWiki" );' >> LocalSettings.php

echo 'wfLoadExtension( "BootstrapComponents" );' >> LocalSettings.php
echo '$wgBootstrapComponentsModalReplaceImageTag = true;' >> LocalSettings.php
