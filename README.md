files_pdfviewer
======

This application integrates the [PDF.js](https://mozilla.github.io/pdf.js/) library into ownCloud. Using this application users can view their PDF files online without downloading the file.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/owncloud/files_pdfviewer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/owncloud/files_pdfviewer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/owncloud/files_pdfviewer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/owncloud/files_pdfviewer/?branch=master)
[![Build Status](https://drone.owncloud.com/api/badges/owncloud/files_pdfviewer/status.svg?branch=master)](https://drone.owncloud.com/owncloud/files_pdfviewer)


Instructions to update pdfjs
===========
1. Prerequisites
- Install npm
- Install bower
- Install gulp

2. Update pdfjs version in bower.json

3. Run `make rebuild-pdfjs`

4. New version of the library will be in `js/vendor/pdfjs`

5. Check if `templates/viewer.php` needs to be updated to match `js/vendor/pdfjs/web/viewer.html`

6. Test the app ;)


Maintainers
===========
- [Lukas Reschke](https://github.com/LukasReschke)
