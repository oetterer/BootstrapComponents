#! /bin/bash

cd mediawiki

php maintenance/importImages.php extensions/BootstrapComponents/tests/resources/ png
php maintenance/runJobs.php --quiet
