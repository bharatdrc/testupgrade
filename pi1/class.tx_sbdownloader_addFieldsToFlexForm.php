<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Sebastian Baumann <sb@sitesystems.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!

* The original function getStorageFolderPid is borrowed from http://www.typo3wizard.com/de/artikel/flexible-content-templavoila-anzeige-im-content-element-wizard.html

***************************************************************/


class tx_sbdownloader_addFieldsToFlexForm{

/**
 * Returning sysfolder ID where records are stored
*/
function getStorageFolderPid() {
	// global $_GET; 	
	$positionPid = \TYPO3\CMS\Core\Utility\GeneralUtility::htmlspecialchars_decode(\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id'));
	// $pid = t3lib_div::_GP('id');
	// print_r($pid);
	if(empty($positionPid)){
		$siteid = \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('returnUrl');
		$siteid = \TYPO3\CMS\Core\Utility\GeneralUtility::explodeUrl2Array($siteid);
		$siteid = $siteid['db_list.php?id'];
		$positionPid = $siteid;
	}
	// print_r($positionPid);
	// Negative PID values is pointing to a page on the same level as the current.
	if ($positionPid<0) {
	$pidRow = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages',abs($positionPid),'pid');
	$positionPid = $pidRow['pid'];
	}
	$row = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecord('pages',$positionPid);
	$TSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getTCEFORM_TSconfig('pages',$row);
	return intval($TSconfig['_STORAGE_PID']);
	}

/**
 * add fields to flexform
*/
 function addFields ($config) {
 	global $TSFE,$LANG;
	$this->storagePid = $this->getStorageFolderPid();
	// print_r($this->storagePid);
	if(!empty($this->storagePid)) {
		$sql = "AND pid=$this->storagePid";
	}else{
		$sql = '';
	}

   $optionList = array();
// exec_SELECTquery 
	// $res = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, "SELECT uid,cat FROM tx_sbdownloader_cat WHERE hidden=0 AND deleted=0 $sql");
	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("uid,cat","tx_sbdownloader_cat","hidden=0 AND deleted=0 $sql");

	$optionList[0] = array(0 => 'all', 1 => 0);
	$i = 1;
//  while($row = mysql_fetch_object($res)){
	while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
    $optionList[$i] = array(0 => $row['cat'], 1 => $row['uid']);
    $i++;
    }

   $config['items'] = array_merge($config['items'],$optionList);

   return $config;
 }
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/pi1/class.tx_sbdownloader_addFieldsToFlexForm.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/pi1/class.tx_sbdownloader_addFieldsToFlexForm.php']);
}


?>
