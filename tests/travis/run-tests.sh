#! /bin/bash
set -ex

originalDirectory=$(pwd)
cd ..
baseDir=$(pwd)
mwDir=mw


cd ${baseDir}/${mwDir}/extensions/BootstrapComponents

if [[ "${TYPE}" == "coverage" ]]; then
	composer unit -- --coverage-clover ${originalDirectory}/build/coverage.clover
elif [[ "${TYPE}" == "integration" ]]; then
	composer integration
elif [[ "${TYPE}" == "unit" ]]; then
	composer unit
else
	composer unit
fi
