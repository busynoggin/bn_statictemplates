<?php

class tx_bnstatictemplates_lib {

	public function addStaticTemplates(&$params, &$parentObject) {
		$row = $params['row'];
		$absoluteConfigurationPath = PATH_site . '/' . $row['tx_bnstatictemplates_path'];
		$relativeConfigurationPath = $row['tx_bnstatictemplates_path'];
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

	public function addStaticTemplateFromPath($path, $title) {
		t3lib_div::loadTCA('sys_template');
		if ($path && is_array($GLOBALS['TCA']['sys_template']['columns'])) {
			$itemArray = array('Busy Noggin: ' . $title, $path);
			return $itemArray;
		}
	}

	public function includeStaticTemplates($params, $parentObject) {
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