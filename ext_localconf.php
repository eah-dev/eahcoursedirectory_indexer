<?php
defined('TYPO3_MODE') or die();

// Register custom indexer.
// Adjust this to your namespace and class name.
// Adjust the autoloading information in composer.json, too!
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerIndexerConfiguration'][] =
    \ErnstAbbeHochschuleJena\EahcoursedirectoryIndexer\Indexer\CourseIndexer::class;
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'][] =
    \ErnstAbbeHochschuleJena\EahcoursedirectoryIndexer\Indexer\CourseIndexer::class;