#!/bin/bash
set -ex

originalDirectory=$(pwd)
cd ..
baseDir=$(pwd)
mwDir=mw


## Use sha (master@5cc1f1d) to download a particular commit to avoid breakages
## introduced by MediaWiki core
if [[ "${MW}" == *@* ]]; then
  arrMw=(${MW//@/ })
  MW=${arrMw[0]}
  SOURCE=${arrMw[1]}
else
 MW=${MW}
 SOURCE=${MW}
fi

if [[ "${MW}" == master ]]; then
 BRANCH=master
else
 BRANCH=${MW%.*}
 BRANCH=REL${BRANCH/./_}
fi

function installMWCoreAndDB {

 echo -e "*** Installing MW Version ${MW}\n"

 cd ${baseDir}
 wget https://github.com/wikimedia/mediawiki/archive/${SOURCE}.tar.gz -O ${MW}.tar.gz
 tar -zxf ${MW}.tar.gz
 mv mediawiki-* ${mwDir}

 cd ${mwDir}

 composer self-update
 composer install --no-suggest

 echo -e "*** Installing database ${DB}\n"

 if [[ "${DB}" == "postgres" ]]; then
  sudo /etc/init.d/postgresql stop
  sudo /etc/init.d/postgresql start

  psql -c 'create database its_a_mw;' -U postgres
  php maintenance/install.php --dbtype ${DB} --dbuser postgres --dbname its_a_mw --pass nyan --scriptpath /TravisWiki TravisWiki admin
 else
  mysql -e 'create database its_a_mw;'
  php maintenance/install.php --dbtype ${DB} --dbuser root --dbname its_a_mw --dbpath $(pwd) --pass nyan --scriptpath /TravisWiki TravisWiki admin
 fi
}

function installSkin {

 echo -e  "*** Installing skin vector\n"
 cd ${baseDir}/${mwDir}/skins

 wget https://github.com/wikimedia/mediawiki-skins-Vector/archive/${BRANCH}.tar.gz -O vector.tar.gz
 tar -zxf vector.tar.gz

 if [[ -e Vector ]]; then
  rm -r Vector # most mw dumps ship with empty skin directories
 fi
 mv mediawiki-skins-Vector-${BRANCH} Vector
}

function installDependencies {

 echo -e "*** Installing Dependencies\n"
 cd ${baseDir}/${mwDir}

 composer require 'mediawiki/semantic-media-wiki=~2.5' --update-with-dependencies --no-suggest
 composer require 'mediawiki/bootstrap=*' --update-with-dependencies

 SCRIB="dev-$BRANCH"
 if [ "$MW" = "master" ]; then
  SCRIB="dev-REL1_30"
 fi
 composer init --stability dev
 composer require 'mediawiki/scribunto' "$SCRIB" --dev --update-with-dependencies
}

function installSourceViaComposer {
 # not used atm
 echo -e "Running composer install build on ${TRAVIS_BRANCH}\n"
 cd ${baseDir}/${mwDir}

 composer require mediawiki/bootstrap-components "dev-master" --dev --update-with-dependencies

 cd /extensions/BootstrapComponents

 # Pull request number, "false" if it's not a pull request
 # After the install via composer an additional get fetch is carried out to
 # update the repository to make sure that the latest code changes are
 # deployed for testing
 if [ "$TRAVIS_PULL_REQUEST" != "false" ]
 then
  git fetch origin +refs/pull/"$TRAVIS_PULL_REQUEST"/merge:
  git checkout -qf FETCH_HEAD
 else
  git fetch origin "$TRAVIS_BRANCH"
  git checkout -qf FETCH_HEAD
 fi

 cd ../..

 # Rebuild the class map for added classes during git fetch
 composer dump-autoload
}

function installSourceFromPull {

 echo -e "*** Installing Extension\n"
 cd ${baseDir}/${mwDir}/extensions

 cp -r ${originalDirectory} BootstrapComponents

 cd ..
 echo 'wfLoadExtension( "BootstrapComponents" );' >> LocalSettings.php
}

function augmentConfiguration {
 echo -e "*** Augmenting LocalSettings\n"

 cd ${baseDir}/${mwDir}

 # Site language
 if [[ "${SITELANG}" != "" ]]; then
  echo '$wgLanguageCode = "'${SITELANG}'";' >> LocalSettings.php
 fi
 echo 'require_once "$IP/extensions/Scribunto/Scribunto.php";' >> LocalSettings.php
 echo '$wgScribuntoDefaultEngine = "luastandalone";' >> LocalSettings.php
 echo '$wgBootstrapComponentsModalReplaceImageTag = true;' >> LocalSettings.php
 echo '$wgEnableUploads = true;' >> LocalSettings.php
 echo 'wfLoadSkin( "Vector" );' >> LocalSettings.php
 echo 'error_reporting(E_ALL| E_STRICT);' >> LocalSettings.php
 echo 'ini_set("display_errors", 1);' >> LocalSettings.php
 echo '$wgShowExceptionDetails = true;' >> LocalSettings.php
 echo '$wgDevelopmentWarnings = true;' >> LocalSettings.php
 echo "putenv( 'MW_INSTALL_PATH=$(pwd)' );" >> LocalSettings.php

 php maintenance/update.php --quick --skip-external-dependencies
}

function injectResources {

 echo -e "*** Uploading test images\n"

 cd ${baseDir}/${mwDir}
 php maintenance/importImages.php ${baseDir}/${mwDir}/extensions/BootstrapComponents/tests/resources/ png
 php maintenance/runJobs.php -s
}


installMWCoreAndDB
installSkin
installDependencies
installSourceFromPull
augmentConfiguration
injectResources
