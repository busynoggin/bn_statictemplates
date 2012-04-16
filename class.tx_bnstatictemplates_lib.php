<?php

class tx_bnstatictemplates_lib {

	public function getMainFields_preProcess($table, $row, $parentObject) {
		if (($table === 'sys_template') && isset($row['tx_bnstatictemplates_path'])) {
			$siteConfigurationPath = PATH_site . '/' . $row['tx_bnstatictemplates_path'];
			$configurations = t3lib_div::get_dirs($siteConfigurationPath);

			foreach ($configurations as $configurationName) {
				$configurationPath = $siteConfigurationPath . '/' . $configurationName;
				if (@is_dir($configurationPath . '/Configuration/TypoScript/')) {
					self::addStaticTemplateFromPath($configurationPath . '/Configuration/TypoScript/', $configurationName);
				}
			}
		}
	}

	public function addStaticTemplateFromPath($path, $title) {
		t3lib_div::loadTCA('sys_template');
		if ($path && is_array($GLOBALS['TCA']['sys_template']['columns'])) {
			$itemArray = array('Busy Noggin: ' . $title, $path);
			$GLOBALS['TCA']['sys_template']['columns']['include_static_file']['config']['items'][] = $itemArray;
		}
	}

	public function includeStaticTemplates($params, $parentObject) {
		$idList = $params['idList'];
		$templateID = $params['templateId'];
		$pid = $params['pid'];
		$row = $params['row'];

		if (trim($row['include_static_file'])) {
			$include_static_fileArr = t3lib_div::trimExplode(',', $row['include_static_file'], TRUE);
			foreach ($include_static_fileArr as $ISF_filePath) { // traversing list
				// Specifically process static templates NOT coming from extensions
				if (substr($ISF_filePath, 0, 4) !== 'EXT:') {
					if (@is_dir($ISF_filePath)) {
						$subrow = array(
							'constants' => @is_file($ISF_filePath . 'Constants.ts') ? t3lib_div::getUrl($ISF_filePath . 'Constants.ts') : '',
							'config' => @is_file($ISF_filePath . 'TypoScript.ts') ? t3lib_div::getUrl($ISF_filePath . 'TypoScript.ts') : '',
							'include_static' => @is_file($ISF_filePath . 'IncludeStatic.txt') ? implode(',', array_unique(t3lib_div::intExplode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStatic.txt')))) : '',
							'include_static_file' => @is_file($ISF_filePath . 'IncludeStaticFile.txt') ? implode(',', array_unique(explode(',', t3lib_div::getUrl($ISF_filePath . 'IncludeStaticFile.txt')))) : '',
							'title' => $ISF_file,
							'uid' => $mExtKey
						);
						$subrow = $parentObject->prependStaticExtra($subrow);
						$parentObject->processTemplate($subrow, $idList . ',bnstatictemplate_' . $ISF_filePath, $pid, 'bnstatictemplate_' . $ISF_filePath, $templateID);
					}
				}
			}
		}

	}
}

?>