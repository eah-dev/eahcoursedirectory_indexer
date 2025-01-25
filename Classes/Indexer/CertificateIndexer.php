<?php
// Set you own vendor name.
// Adjust the extension name part of the namespace to your extension key.
namespace ErnstAbbeHochschuleJena\EahcoursedirectoryIndexer\Indexer;

use Tpwd\KeSearch\Indexer\IndexerBase;
use Tpwd\KeSearch\Indexer\IndexerRunner;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

// Set you own class name.
class CertificateIndexer extends IndexerBase
{
    // Set a key for your indexer configuration.
    // Add this key to the $GLOBALS[...] array in Configuration/TCA/Overrides/tx_kesearch_indexerconfig.php, too!
    // It is recommended (but no must) to use the name of the table you are going to index as a key because this
    // gives you the "original row" to work with in the result list template.
    const KEY = 'tx_eahcoursedirectory_domain_model_certificate';

    // The database table you want to index, it can be the same as the KEY but that's no must (although recommended).
    const TABLE = 'tx_eahcoursedirectory_domain_model_certificate';

    /**
     * Adds the custom indexer to the TCA of indexer configurations, so that
     * it's selectable in the backend as an indexer type, when you create a
     * new indexer configuration.
     *
     * @param array $params
     * @param object $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        // Set a name and an icon for your indexer.
        $customIndexer = array(
            'certificate (ext:eahcoursedirectory)',
            CertificateIndexer::KEY,
            'EXT:eahcoursedirectory_indexer/Resources/Public/Icons/Extension.svg'
        );
        $params['items'][] = $customIndexer;
    }

    /**
     * Custom indexer for ke_search.
     *
     * @param   array $indexerConfig Configuration from TYPO3 Backend.
     * @param   IndexerRunner $indexerObject Reference to indexer class.
     * @return  string Message containing indexed elements.
     */
    public function customIndexer(array &$indexerConfig, IndexerRunner &$indexerObject): string
    {
        if ($indexerConfig['type'] == CertificateIndexer::KEY)
        {
            // Get the indexer status service if available (since ke_search 5.4.0)
            if (class_exists('\Tpwd\KeSearch\Service\IndexerStatusService')) {
                $indexerStatusService = GeneralUtility::makeInstance(\Tpwd\KeSearch\Service\IndexerStatusService::class);
            }

            // Doctrine DBAL using Connection Pool.
            /** @var Connection $connection */
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);
            $queryBuilder = $connection->createQueryBuilder();

            if (empty($indexerConfig['sysfolder'])) {
                throw new \Exception('No folder specified. Please set the folder which should be indexed in the indexer configuration!');
            }

            // Handle restrictions.
            // Don't fetch hidden or deleted elements, but the elements
            // with frontend user group access restrictions or time (start / stop)
            // restrictions in order to copy those restrictions to the index.
            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

            $folders = GeneralUtility::trimExplode(',', htmlentities($indexerConfig['sysfolder']));
            $query = $queryBuilder
                ->select('*')
                ->from(self::TABLE)
                ->where($queryBuilder->expr()->in( 'pid', $folders))
                ->executeQuery();

            // Loop through the records and write them to the index.
            $counter = 0;
            $totalCount = $query->rowCount();
            while ($record = $query->fetchAssociative())
            {
                if (isset($indexerStatusService)) {
                    $indexerStatusService->setRunningStatus($indexerConfig, $counter, $totalCount);
                }
                // Compile the information, which should go into the index.
                // The field names depend on the table you want to index!
                $title    = strip_tags($record['name'] ?? '');
                $abstract = strip_tags($record['teasertext'] ?? '');
                $content  = strip_tags($record['teasertext'] ?? '');


                // add a fields for the index
                $content .= strip_tags($record['addinfos'] ?? '');
                $content .= strip_tags($record['keyfacts'] ?? '');
                $content .= strip_tags($record['professionalperspectives'] ?? '');
                $content .= strip_tags($record['structure_text'] ?? '');
                $content .= strip_tags($record['coursespecializationpagetitle'] ?? '');
                $content .= strip_tags($record['coursespecializationpagetext'] ?? '');

                $fullContent = $title . "\n" . $abstract . "\n" . $content;
                //$fullContent = $title . "\n" . $abstract;

                // Link to detail view
                $params = '&tx_eahcoursedirectory_certificate[certificate]=' . $record['uid']
                    . '&tx_eahcoursedirectory_certificate[controller]=Certificate&tx_eahcoursedirectory_certificate[action]=show';

                // Tags
                // If you use Sphinx, use "_" instead of "#" (configurable in the extension manager).
                $tags = '';

                // Additional information
                $additionalFields = array(
                    'orig_uid' => $record['uid'],
                    'orig_pid' => $record['pid'],
                    'sortdate' => $record['crdate'] ?? 0,
                );

                // set custom sorting
                //$additionalFields['mysorting'] = $counter;

                // Add something to the title, just to identify the entries
                // in the frontend.
                //$title = '[certificate INDEXER] ' . $title;

                // ... and store the information in the index
                $indexerObject->storeInIndex(
                    $indexerConfig['storagepid'],   // storage PID
                    $title,                         // record title
                    CertificateIndexer::KEY,            // content type
                    $indexerConfig['targetpid'],    // target PID: where is the single view?
                    $fullContent,                   // indexed content, includes the title (linebreak after title)
                    $tags,                          // tags for faceted search
                    $params,                        // typolink params for singleview
                    $abstract,                      // abstract; shown in result list if not empty
                    $record['sys_language_uid'],    // language uid
                    $record['starttime'],           // starttime
                    $record['endtime'],             // endtime
                    $record['fe_group'] ?? '',      // fe_group
                    false,                          // debug only?
                    $additionalFields               // additionalFields
                );

                $counter++;
            }

            $content = $counter . ' Elements have been indexed.';

            return $content;
        }
    return '';
    }
}
