<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'pi1/class.tx_sbdownloader_addFieldsToFlexForm.php');




// insert CSS file
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY,'Configuration/TypoScript/','downloader');

//t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/Configuration/FlexForms/flexform.xml');
// you add pi_flexform to be renderd when your plugin is shown
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';                   // new!
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(array('LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');



// t3lib_div::loadTCA("pages"); 

?>
