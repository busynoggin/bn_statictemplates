<?php

class tx_bnstatictemplates_lib {

	const PATH_TYPE_BASE = 0;
	const PATH_TYPE_SITE = 1;

	/**
	 * ItemsProcFunc for adding static templates from fileadmin.
	 *
	 * @param array $params
	 * @param object parentObject
	 */
	public static function addStaticTemplates(&$params, &$parentObject) {
		if (!is_array($params['items'])) {
			$params['items'] = array();
		}

		$baseStaticTemplates = self::getStaticTemplatesInBaseConfigurationPath();
		$siteStaticTemplates = self::getStaticTemplatesInSiteConfigurationPath($params['row']['pid']);

		$mergedItems = array();
		$mergedItems['items'] = array_merge($baseStaticTemplates['items'], $siteStaticTemplates['items']);
		usort($mergedItems['items'], function($a, $b) {
			return $a[0] > $b[0];
		});

		$params['items'] = array_merge($params['items'], $mergedItems['items']);
	}

	/**
	 * Gets the base configuration path from EXTCONF
	 *
	 * @return string
	 */
	protected static function getBaseConfigurationPath() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['bn_statictemplates']);
		return $extConf['baseConfigurationPath'];
	}

	/**
	 * Gets the items array for static templates in the base configuration path
	 *
	 * @return array
	 */
	protected static function getStaticTemplatesInBaseConfigurationPath() {
		$relativeConfigurationPath = self::getBaseConfigurationPath();
		return self::getStaticTemplatesInPath(self::PATH_TYPE_BASE, $relativeConfigurationPath);
	}

	/**
	 * Gets the site configuration path for the given page ID
	 *
	 * @param integer $pageId
	 * @return string
	 */
	protected static function getSiteConfigurationPath($pageId) {
		$tmpl = t3lib_div::makeInstance("t3lib_tsparser_ext");
		$tmpl->tt_track = 0;
		$tmpl->init();

		// local template
		$localTemplateRow = $tmpl->ext_getFirstTemplate($pageId);

		// Gets the rootLine
		$sys_page = t3lib_div::makeInstance("t3lib_pageSelect");
		$rootLine = $sys_page->getRootLine($pageId);
		$tmpl->runThroughTemplates($rootLine, $localTemplateRow['uid']);

		// Use the root page found when walking the rootline
		$templateRow = $tmpl->ext_getFirstTemplate($tmpl->rootId);

		return $templateRow['tx_bnstatictemplates_path'];
	}

	/**
	 * Gets the items array for the static templates in the site configuration
	 *
	 * @return array
	 */
	protected static function getStaticTemplatesInSiteConfigurationPath($pid) {
		$relativeConfigurationPath = self::getSiteConfigurationPath($pid);
		return self::getStaticTemplatesInPath(self::PATH_TYPE_SITE, $relativeConfigurationPath);
	}

	/**
	 * Gets the items array for a given configuration type (base or site) and path.
	 *
	 * @param integer $configurationType
	 * @param string $relativeConfigurationPath
	 * @return array
	 */
	protected static function getStaticTemplatesInPath($configurationType, $relativeConfigurationPath) {
		$configurations = t3lib_div::get_dirs(PATH_site . rtrim($relativeConfigurationPath, '/') . '/Extensions/');

		$params = array();
		$params['items'] = array();
		foreach ((array) $configurations as $configurationKey) {
			$pathToTS = trim($relativeConfigurationPath, '/') . '/Extensions/' . $configurationKey . '/Configuration/TypoScript/';
			if (@is_dir(PATH_site . $pathToTS)) {
				switch($configurationType) {
					case self::PATH_TYPE_BASE:
						$configurationName = $configurationKey . ' (Base)';
						break;
					case self::PATH_TYPE_SITE:
						$configurationName = $configurationKey . ' (Site)';
				}

				$params['items'][] = array('BN: ' . $configurationName, $pathToTS);
			}
		}

		return $params;
	}

	/**
	 * Includes static templates as part of the page rendering process.
	 *
	 * @param array $params
	 * @param object $parentObject
	 * @return void
	 */
	public static function includeStaticTemplates($params, $parentObject) {
		$idList = $params['idList'];
		$templateId = $params['templateId'];
		$pid = $params['pid'];
		$row = $params['row'];

		if (trim($row['include_static_file'])) {
			$include_static_fileArr = t3lib_div::trimExplode(',', $row['include_static_file'], TRUE);
			foreach ((array) $include_static_fileArr as $ISF_filePath) {
				// Specifically process static templates NOT coming from extensions and have not already been processed
				if ((substr($ISF_filePath, 0, 4) !== 'EXT:') && !in_array('bnstatictemplate_' . $ISF_filePath, explode(',', $idList))) {
					$title = $ISF_filePath;
					$ISF_relFilePath = $ISF_filePath;
					$ISF_filePath = PATH_site . $ISF_filePath;
					if (@is_dir($ISF_filePath)) {
						// Convert IncludeStaticFile.txt to an array
						if (@is_file($ISF_filePath . 'IncludeStaticFile.txt')) {
							$staticFilesIncludedFromTemplate = array_unique(explode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStaticFile.txt')));
						} else {
							$staticFilesIncludedFromTemplate = array();
						}

						$baseConfigurationPath = self::getBaseConfigurationPath();
						$siteConfigurationPath = self::getSiteConfigurationPath($parentObject->rootId);

						// If we're including something from site configuraton, look for a corresponding base configuration to include
						if (strstr(rtrim(dirname($ISF_filePath), '/'), rtrim(PATH_site . $siteConfigurationPath, '/')) !== FALSE) {
							$baseConfiguration = str_replace($siteConfigurationPath, $baseConfigurationPath, $ISF_relFilePath);
							if (@is_dir(PATH_site . $baseConfiguration)) {
								$staticFilesIncludedFromTemplate[] = $baseConfiguration;
							}
						}

						$subrow = array(
							'constants' => @is_file($ISF_filePath . 'Constants.ts') ? t3lib_div::getUrl($ISF_filePath . 'Constants.ts') : '',
							'config' => @is_file($ISF_filePath . 'TypoScript.ts') ? t3lib_div::getUrl($ISF_filePath . 'TypoScript.ts') : '',
							'include_static' => @is_file($ISF_filePath . 'IncludeStatic.txt') ? implode(',', array_unique(t3lib_div::intExplode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStatic.txt')))) : '',
							'include_static_file' => implode(',', array_unique($staticFilesIncludedFromTemplate)),
							'title' => $ISF_relFilePath,
							'uid' => $ISF_relFilePath
						);

						$parentObject->processTemplate($subrow, $idList . ',bnstatictemplate_' . $ISF_relFilePath, $pid, 'bnstatictemplate_' . $ISF_relFilePath, $templateId);
					}
				}
			}
		}
	}

	/**
	 * Checks if the given path is within the site static TSConfig path
	 *
	 * @param string $path
	 * @return boolean
	 */
	protected static function isPathWithinSiteStaticTSConfigPath($path) {
		$siteStaticTSConfigPath = self::getSiteStaticTSConfigPath();
		return (strstr($path, $siteStaticTSConfigPath) !== FALSE);
	}
}

?>