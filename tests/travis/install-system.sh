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

 echo "Installing MW Version ${MW}"

 cd ${baseDir}
 wget https://github.com/wikimedia/mediawiki/archive/${SOURCE}.tar.gz -O ${MW}.tar.gz
 tar -zxf ${MW}.tar.gz
 mv mediawiki-* ${mwDir}

 cd ${mwDir}

 composer self-update
 composer install --prefer-source

 echo "installing database ${DB}"

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

 echo "installing skin vector"
 cd ${baseDir}/${mwDir}/skins

 wget https://github.com/wikimedia/mediawiki-skins-Vector/archive/${BRANCH}.tar.gz -O vector.tar.gz
 tar -zxf vector.tar.gz

 if [[ -e Vector ]]; then
  rm -r Vector # REL1_30 has an empty Vector directory sitting here (as well as CologneBlue, Modern, and MonoBook
 fi
 mv mediawiki-skins-Vector-${BRANCH} Vector
}

function installSourceViaComposer {
 echo "missing"
}

function installSourceFromPull {

 echo "Installing Extension"
 cd ${baseDir}/${mwDir}

 composer require 'mediawiki/semantic-media-wiki=~2.5' --update-with-dependencies
 composer require 'mediawiki/bootstrap=*' --update-with-dependencies

 cd extensions

 cp -r ${originalDirectory} BootstrapComponents

 cd ..
 echo 'wfLoadExtension( "BootstrapComponents" );' >> LocalSettings.php
}

function augmentConfiguration {

 cd ${baseDir}/${mwDir}

 # Site language
 if [[ "${SITELANG}" != "" ]]; then
  echo '$wgLanguageCode = "'${SITELANG}'";' >> LocalSettings.php
 fi
 echo '$wgBootstrapComponentsModalReplaceImageTag = true;' >> LocalSettings.php
 #echo '$wgBootstrapComponentsDisableIdsForTestEnvironment = true;' >> LocalSettings.php
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

 echo "Uploading test images"

 cd ${baseDir}/${mwDir}
 php maintenance/importImages.php ${baseDir}/${mwDir}/extensions/BootstrapComponents/tests/resources/ png
 php maintenance/runJobs.php -s
}


installMWCoreAndDB
installSkin
installSourceFromPull
augmentConfiguration
injectResources