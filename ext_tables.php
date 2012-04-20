<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
$tempColumns = array(
	'tx_bnstatictemplates_path' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:bn_statictemplates/locallang_db.xml:sys_template.tx_bnstatictemplates_path',
		'displayCond' => 'FIELD:root:REQ:true',
		'config' => array(
			'type'     => 'input',
			'size'     => '15',
			'max'      => '255',
			'eval'     => 'trim',
			'wizards'  => array(
				'_PADDING' => 2,
				'link'     => array(
					'type'         => 'popup',
					'title'        => 'Link',
					'icon'         => 'link_popup.gif',
					'script'       => 'browse_links.php?mode=wizard',
					'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1'
				)
			)
		)
	),
);


t3lib_div::loadTCA('sys_template');
t3lib_extMgm::addTCAcolumns('sys_template',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('sys_template','tx_bnstatictemplates_path;;;;1-1-1');

require_once(t3lib_extMgm::extPath('bn_statictemplates') . 'class.tx_bnstatictemplates_lib.php');
$TCA['sys_template']['columns']['include_static_file']['config']['itemsProcFunc'] = 'tx_bnstatictemplates_lib->addStaticTemplates';

?>