<?php
namespace HENRIKBRAUNE\SeoBasicsPluginSitemap\Hooks;

    /***************************************************************
     *  Copyright notice
     *
     *  (c) 2014 Henrik Braune <henrik@braune.org>, HENRIK BRAUNE
     *
     *  All rights reserved
     *
     *  This script is part of the TYPO3 project. The TYPO3 project is
     *  free software; you can redistribute it and/or modify
     *  it under the terms of the GNU General Public License as published by
     *  the Free Software Foundation; either version 3 of the License, or
     *  (at your option) any later version.
     *
     *  The GNU General Public License can be found at
     *  http://www.gnu.org/copyleft/gpl.html.
     *
     *  This script is distributed in the hope that it will be useful,
     *  but WITHOUT ANY WARRANTY; without even the implied warranty of
     *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     *  GNU General Public License for more details.
     *
     *  This copyright notice MUST APPEAR in all copies of the script!
     ***************************************************************/

/**
 *
 *
 * @package seo_basics_plugin_sitemap
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Sitemap {

    /**
     * @param array $params
     */
    public function setAdditionalUrls($params) {

        $cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer');
        $plugins = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_seobasicspluginsitemap.']['extensions.'];

        foreach($plugins as $plugin => $configuration) {

            if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded(substr($plugin, 0, -1))) {
                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
                    implode(',', $configuration['fields.']),
                    $configuration['table'],
                    'hidden = 0 AND deleted = 0'
                );

                $additionalParams = array();
                foreach ($configuration['additionalParams.'] as $param) {
                    $pair = explode('=', $param);
                    $additionalParams[$pair[0]] = $pair[1];
                }

                if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {
                    while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){

                        $uniqueAdditionalParams = array();
                        foreach($additionalParams as $paramName => $paramValue) {
                            $uniqueAdditionalParams[$paramName] = (substr($paramValue, 0, 1) == '$') ? $row[substr($paramValue, 1)] : $paramValue;
                        }

                        $link = $cObj->getTypoLink_URL($configuration['detailPid'], $uniqueAdditionalParams);

                        if ($row[$configuration['fields.']['tstamp']]) {
                            $lastmod = '<lastmod>' . htmlspecialchars(date('c', $row[$configuration['fields.']['tstamp']])) . '</lastmod>';
                        } else {
                            $lastmod = '';
                        }

                        $params['content'] .= '
                            <url>
                                <loc>' . htmlspecialchars($link) . '</loc>' . $lastmod . '
                            </url>
                        ';
                    }
                }
            }
        }
    }
}
?>