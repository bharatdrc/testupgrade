<?php


return array(
	"ctrl" => array (
		'title'     => 'LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images',
		'label'     => 'name',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE, 
		'origUid' => 't3_origuid',
		'searchFields' => 'linkname',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l18n_parent',
		'transOrigDiffSourceField' => 'l18n_diffsource',
		// 'default_sortby' => "ORDER BY name",
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		//'dynamicConfigFile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'tca.php',
		'iconfile'          => 'EXT:sb_downloader/Resources/Public/Icons/icon_tx_sbdownloader_images.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, cat, name, image, imagepreview, linkdescription, downloaddescription, description, longdescription, metatags, clicks,related",
	),
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,image,externallinks,description,longdescription,clicks,cat,shortlink,related"
	),
	"feInterface" => $TCA["tx_sbdownloader_images"]["feInterface"],
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
				'foreign_table'       => 'tx_sbdownloader_images',
				'foreign_table_where' => 'AND tx_sbdownloader_images.pid=###CURRENT_PID### AND tx_sbdownloader_images.sys_language_uid IN (-1,0)',
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
		'usercheck' => array (
			'exclude' => 1,
			'label'   => 'LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.usercheck',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),		
		"name" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.name",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		"image" => Array (
			"exclude" => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.image",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",
				"disallowed" => "php,php3",
				"max_size" => 1000000,
				"uploadfolder" => "uploads/tx_sbdownloader",
				"show_thumbs" => 1,
				"size" => 10,
				"minitems" => 0,
				"maxitems" => 40,
			)
		),
		"externallinks" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.externallinks",
			"config" => Array (
				"type" => "text",
				"cols" => 30,
				"rows" => 5,
			)
		),
		"imagepreview" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.imagepreview",
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "jpg,gif,png",
				"max_size" => 10000,
				"uploadfolder" => "uploads/tx_sbdownloader",
				"show_thumbs" => 1,
				"size" => 1,
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"downloaddescription" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.imagedescription",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"metatags" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.metatags",
			"config" => Array (
				"type" => "text",
				"cols" => "50",
				"rows" => "5",
			)
		),		
		"linkdescription" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.linkdescription",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"description" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.description",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"longdescription" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.longdescription",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						//"script" => "wizard_rte.php",
						'module' => array(
							'name' => 'wizard_rte',
						),
					),
				),
			)
		),
		"clicks" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.clicks",
			"config" => Array (
				"type" => "none",
			)
		),
		"shortlink" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.shortlink",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
		
		"related" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.related",
			"config" => Array (
				"type" => "group",	
				"internal_type" => "db",	
				"allowed" => "tx_sbdownloader_images",					
				"size" => 5,
				"minitems" => 0,
				"maxitems" => 10,
			)
		),			
        'cat' => Array (
            'exclude' => 1,
			'l10n_mode' => 'exclude',
            'label' => 'LLL:EXT:sb_downloader/Resources/Private/Language/Backend/locallang_db.xml:tx_sbdownloader_images.cat',
			/*'config' => Array (
				'type' => 'select',
				'form_type' => 'user',
				'userFunc' => 'tx_sbdownloader_treeview->displayHierarchyTree',
				'treeView' => 1,
				'size' => 10,
				'autoSizeMax' => 10,
				'minitems' => 0,
				'maxitems' => 4,
				'disableNoMatchingValueElement' => 0,
				'foreign_table' => 'tx_sbdownloader_cat',
                'foreign_table_where' => "AND tx_sbdownloader_cat.pid=###STORAGE_PID### AND tx_sbdownloader_cat.sys_language_uid IN (-1,0) ORDER BY tx_sbdownloader_cat.cat",
				'MM' => 'tx_sbdownloader_images_cat_mm',
				'table_MM' => 'tx_sbdownloader_images_parent_cat_mm',
			),*/
			'config' => [
				'type' => 'select',
				'renderType' => 'selectMultipleSideBySide',
				'foreign_table' => 'tx_sbdownloader_cat',
				'MM' => 'tx_sbdownloader_images_cat_mm',
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
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden, usercheck;;1, cat, name, metatags, image, externallinks, imagepreview,linkdescription, downloaddescription, description,longdescription;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], clicks,shortlink,related")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);