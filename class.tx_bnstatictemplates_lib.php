<?php

class tx_bnstatictemplates_lib {

	/**
	 * ItemsProcFunc for adding static templates from fileadmin.
	 *
	 * @param array $params
	 * @param object parentObject
	 */
	public static function addStaticTemplates(&$params, &$parentObject) {
		$row = $params['row'];
		$relativeConfigurationPath = self::getStaticTemplatePath($row['pid']);
		$absoluteConfigurationPath = PATH_site . '/' . $relativeConfigurationPath;
		$configurations = t3lib_div::get_dirs($absoluteConfigurationPath);

		foreach ($configurations as $configurationName) {
			if (@is_dir($absoluteConfigurationPath . $configurationName . '/Configuration/TypoScript/')) {
				$itemArray = self::addStaticTemplateFromPath($relativeConfigurationPath . $configurationName . '/Configuration/TypoScript/', $configurationName);

				if ($itemArray) {
					$params['items'][] = $itemArray;
				}
			}
		}

		return $params['items'];
	}

	public static function getStaticTemplatePath($pageId) {
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

	public static function addStaticTemplateFromPath($path, $title) {
		t3lib_div::loadTCA('sys_template');
		if ($path && is_array($GLOBALS['TCA']['sys_template']['columns'])) {
			$itemArray = array('Busy Noggin: ' . $title, $path);
			return $itemArray;
		}
	}

	public static function includeStaticTemplates($params, $parentObject) {
		$idList = $params['idList'];
		$templateId = $params['templateId'];
		$pid = $params['pid'];
		$row = $params['row'];

		if (trim($row['include_static_file'])) {
			$include_static_fileArr = t3lib_div::trimExplode(',', $row['include_static_file'], TRUE);
			foreach ($include_static_fileArr as $ISF_filePath) { // traversing list
				// Specifically process static templates NOT coming from extensions
				if (substr($ISF_filePath, 0, 4) !== 'EXT:') {
					$title = $ISF_filePath;
					$ISF_filePath = PATH_site . $ISF_filePath;
					if (@is_dir($ISF_filePath)) {
						$subrow = array(
							'constants' => @is_file($ISF_filePath . 'Constants.ts') ? t3lib_div::getUrl($ISF_filePath . 'Constants.ts') : '',
							'config' => @is_file($ISF_filePath . 'TypoScript.ts') ? t3lib_div::getUrl($ISF_filePath . 'TypoScript.ts') : '',
							'include_static' => @is_file($ISF_filePath . 'IncludeStatic.txt') ? implode(',', array_unique(t3lib_div::intExplode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStatic.txt')))) : '',
							'include_static_file' => @is_file($ISF_filePath . 'IncludeStaticFile.txt') ? implode(',', array_unique(explode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStaticFile.txt')))) : '',
							'title' => $title,
							'uid' => $mExtKey
						);
						$subrow = $parentObject->prependStaticExtra($subrow);
						$parentObject->processTemplate($subrow, $idList . ',bnstatictemplate_' . $ISF_filePath, $pid, 'bnstatictemplate_' . $ISF_filePath, $templateId);
					}
				}
			}
		}
	}
}

?>