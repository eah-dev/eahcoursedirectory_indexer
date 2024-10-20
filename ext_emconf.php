<?php

/*
 * This file is part of the package ErnstAbbeHochschuleJena/EahcoursedirectoryIndexer.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 *
 * (c) 2022 Carsten Hoelbing <carsten.hoelbing@eah-jena.de>, Ernst-Abbe-Hochschule Jena
 *
 */

 $EM_CONF[$_EXTKEY] = [
    'title' => 'ke_search indexer for eahcoursedirectory',
    'description' => 'Indexer for ke_search which indexed eahcoursedirectory records',
    'category' => 'plugin',
    'author' => 'Carsten Hoelbing',
    'author_email' => 'carsten.hoelbing@eah-jena.de',
    'state' => 'stable',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
            'eahcoursedirectory' => '2.0.0-0.0.0',
            'ke_search' => '5.0.0-6.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'ErnstAbbeHochschuleJena\\EahcoursedirectoryIndexer\\' => 'Classes',
        ],
    ],
];
