#! /bin/bash
set -ex

originalDirectory=$(pwd)
cd ..
baseDir=$(pwd)
mwDir=mw


cd ${baseDir}/${mwDir}/extensions/BootstrapComponents

if [[ "${TYPE}" == "coverage" ]]; then
	composer phpunit -- --coverage-clover ${originalDirectory}/build/coverage.clover
else
	composer phpunit
fi
