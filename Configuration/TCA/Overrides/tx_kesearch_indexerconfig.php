<?php
defined('TYPO3') or die();

// enable "startingpoints_recursive" field
$GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['startingpoints_recursive']['displayCond'] .= ',tx_eahcoursedirectory_domain_model_course';

// enable "sysfolder" field
$GLOBALS['TCA']['tx_kesearch_indexerconfig']['columns']['sysfolder']['displayCond'] .= ',' . \ErnstAbbeHochschuleJena\EahcoursedirectoryIndexer\Indexer\CourseIndexer::KEY;
