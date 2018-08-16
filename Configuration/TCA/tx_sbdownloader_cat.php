<?php
if (TYPO3_MODE=='BE'){
	// class for displaying the unit tree in BE forms.
	include_once(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('sb_downloader').'class.tx_sbdownloader_treeview.php');
}
return array(
	"ctrl" => array (
		'title'     => 'LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_cat',
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
		'iconfile'          => 'EXT:sb_downloader/Resources/Public/Icons/icon_tx_sbdownloader_cat.gif',
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
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'renderType' => 'selectSingle',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
						-1
					),
					array(
						'LLL:EXT:lang/locallang_general.xlf:LGL.default_value',
						0
					)
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
			'label'   => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"cat" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_cat.cat",
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
			/*'config' => Array (
				'type' => 'select',
				//'form_type' => 'user',
				//'itemsProcFunc' => 'tx_sbdownloader_treeview->displayHierarchyTree',
				//'treeView' => 1,
				'size' => 10,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 10,
				'foreign_table' => 'tx_sbdownloader_cat',
                'foreign_table_where' => 'AND tx_sbdownloader_cat.pid=###STORAGE_PID###',
				'MM' => 'tx_sbdownloader_images_parent_cat_mm',
				'table_MM' => 'tx_sbdownloader_images_parent_cat_mm',
			),	*/
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_sbdownloader_cat',
				'MM' => 'tx_sbdownloader_images_parent_cat_mm',
				'size' => 10,
				'autoSizeMax' => 30,
				'minitems' => 0,
				'maxitems' => 9999,
				'multiple' => 0,
				'wizards' => [
					'_PADDING' => 1,
					'_VERTICAL' => 1,
					'edit' => [
						'module' => [
							'name' => 'wizard_edit',
						],
						'type' => 'popup',
						'title' => 'Edit', // todo define label: LLL:EXT:.../Resources/Private/Language/locallang_tca.xlf:wizard.edit
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_edit.gif',
						'popup_onlyOpenIfSelected' => 1,
						'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
					],
					'add' => [
						'module' => [
							'name' => 'wizard_add',
						],
						'type' => 'script',
						'title' => 'Create new', // todo define label: LLL:EXT:.../Resources/Private/Language/locallang_tca.xlf:wizard.add
						'icon' => 'EXT:backend/Resources/Public/Images/FormFieldWizard/wizard_add.gif',
						'params' => [
							'table' => 'tx_sbdownloader_cat',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						],
					],
				],
			],

        ),		
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, cat, parent_cat")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);