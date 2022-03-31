#! /bin/bash

php maintenance/importImages.php extensions/BootstrapComponents/tests/resources/ png
php maintenance/runJobs.php --quiet
