<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
	options.saveDocNew.tx_sbdownloader_images=1
');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig('
	options.saveDocNew.tx_sbdownloader_cat=1
');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_sbdownloader_pi1 = < plugin.tx_sbdownloader_pi1.CSS_editor
',43);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43($_EXTKEY,'pi1/class.tx_sbdownloader_pi1.php','_pi1','list_type',0);

// HOOK registrieren
// $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][]  =  'EXT:sfmyext/hook/class.tx_sfmyext_preproc.php:tx_sfmyext_preproc';

// Hook for sb_userstats
// $TYPO3_CONF_VARS['EXTCONF']['sb_downloader']['hook'][] = 'EXT:sb_userstats/class.sbuserstats_hooks.php:sbuserstats_hook';
?>