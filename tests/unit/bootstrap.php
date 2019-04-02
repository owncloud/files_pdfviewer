<?php

if (!\defined('PHPUNIT_RUN')) {
	\define('PHPUNIT_RUN', 1);
}

require_once __DIR__.'/../../../../lib/base.php';

\OC::$composerAutoloader->addPsr4('Test\\', OC::$SERVERROOT . '/tests/lib/', true);

\OC::$loader->addValidRoot(\OC::$SERVERROOT . '/tests');
\OC_App::loadApp('files_pdfviewer');

OC_Hook::clear();
