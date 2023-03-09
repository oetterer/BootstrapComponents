#! /bin/bash

COVERAGE_REPORT=${MW_ROOT}/coverage.clover

if [[ ! -e ${COVERAGE_REPORT} ]]; then
	echo "File coverage.clover is missing. Abort the upload!"
	exit 127
fi

cd EarlyCopy

wget https://scrutinizer-ci.com/ocular.phar
php ocular.phar code-coverage:upload --format=php-clover ../${COVERAGE_REPORT}
