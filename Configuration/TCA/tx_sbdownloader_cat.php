<?php

return array(
	"ctrl" => array (
		'title'     => 'LLL:EXT:sb_downloader/locallang_db.xml:tx_sbdownloader_cat',
		'label'     => 'cat',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'searchFields' => 'cat',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		'default_sortby' => "ORDER BY crdate",
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		//'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'tca.php',
		'iconfile'          => 'EXT:sb_downloader/icon_tx_sbdownloader_cat.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, cat, parent_cat",
	),
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,cat"
	),
	"feInterface" => $TCA["tx_sbdownloader_cat"]["feInterface"],
	"columns" => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => 30,
				'max'  => 30,
			)
		),
		'sys_language_uid' => array (
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_sbdownloader_cat',
				'foreign_table_where' => 'AND tx_sbdownloader_cat.pid=###CURRENT_PID### AND tx_sbdownloader_cat.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"cat" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/locallang_db.xml:tx_sbdownloader_cat.cat",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
        'parent_cat' => Array (
            'exclude' => 1,
			'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:sb_downloader/locallang_db.php:tx_sbdownloader_cat.parent_cat',
			// 'config' => Array (
				// 'type' => 'select',
				// 'form_type' => 'user',
				// 'userFunc' => 'tx_riorganisation_treeview->displayHierarchyTree',
				// 'treeView' => 1,
				// 'size' => 10,
				// 'autoSizeMax' => 10,
				// 'minitems' => 0,
				// 'maxitems' => 10,
				// 'foreign_table' => 'tx_riorganisation_businessunit',
                // 'foreign_table_where' => $fTableWhere,
				// 'MM' => 'tx_riorganisation_businessunit_parent_bu_mm',
				// 'table_MM' => 'tx_riorganisation_businessunit_parent_bu_mm',
			// ),	
			'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_sbdownloader_treeview->displayHierarchyTree',
				'treeView' => 1,
				'size' => 10,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'tx_sbdownloader_cat',
                'foreign_table_where' => 'AND tx_sbdownloader_cat.pid=###STORAGE_PID###',
				'MM' => 'tx_sbdownloader_images_parent_cat_mm',
				'table_MM' => 'tx_sbdownloader_images_parent_cat_mm',
			),			
        ),		
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, cat, parent_cat")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);