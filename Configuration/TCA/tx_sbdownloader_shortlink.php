<?php

return array(
	"ctrl" => array (
		'title'     => 'LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_shortlink',
		'label'     => 'cat',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'searchFields' => 'linkname',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		//'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'tca.php',
		'iconfile'          => 'EXT:sb_downloader/Resources/Public/Icons/icon_tx_sbdownloader_cat.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, cat, parent_cat",
	),
	"interface" => array (
		"showRecordFieldList" => "linkname"
	),	
	"columns" => array (	
		
	),	
	"types" => array (
		"0" => array("showitem" => "linkname")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);