<?php
defined('TYPO3_MODE') or die();

$tempColumns = array(
	'tx_bnstatictemplates_path' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:bn_statictemplates/locallang_db.xlf:sys_template.tx_bnstatictemplates_path',
		'displayCond' => 'FIELD:root:REQ:true',
		'config' => array(
			'type'     => 'input',
			'size'     => '30',
			'max'      => '255',
			'eval'     => 'trim',
			'wizards'  => array(
				'_PADDING' => 2,
				'link'     => array(
					'type'         => 'popup',
					'title'        => 'Link',
					'icon'         => 'link_popup.gif',
					'script'       => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
					'params'       => array(
						'blindLinkOptions' => 'mail,page,spec,url,file'
					)
				)
			)
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_template', $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_template', 'tx_bnstatictemplates_path;;;;1-1-1', '', 'before:include_static_file');

$GLOBALS['TCA']['sys_template']['columns']['include_static_file']['config']['itemsProcFunc'] = \BusyNoggin\BnStatictemplates\StaticTemplateLibrary::class . '->addStaticTemplates';
