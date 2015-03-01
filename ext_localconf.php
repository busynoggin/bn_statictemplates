<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Process non-extension sources of static templates
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['includeStaticTypoScriptSourcesAtEnd'][] = \BusyNoggin\BnStatictemplates\StaticTemplateLibrary::class . '->includeStaticTemplates';

?>