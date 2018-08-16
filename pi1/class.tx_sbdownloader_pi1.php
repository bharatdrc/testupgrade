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
***************************************************************/
// if (class_exists('tslib_pibase')) {
	// require_once(PATH_tslib.'class.tslib_pibase.php');
// }else{
	// require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('frontend') . 'Classes/Plugin/AbstractPlugin.php';
// }
//if (!class_exists('tslib_pibase')) require_once(PATH_tslib . 'class.tslib_pibase.php');


/**
 * Plugin 'Image Downloader' for the 'sb_downloader' extension.
 * with hook from Markus Dreyer <Markus@MadaXel.de>
 * with addons from Kurt Kunig kurt@kupix.de (Grafic of download/date & time)
 * extended and rewritten by Robert Heel <typo3.org@bobosch.de>
 * @author	Sebastian Baumann <sb@sitesystems.de>
 * @package	TYPO3
 * @subpackage	tx_sbdownloader
 */
class tx_sbdownloader_pi1 extends \TYPO3\CMS\Frontend\Plugin\AbstractPlugin {
	var $prefixId      = 'tx_sbdownloader_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_sbdownloader_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'sb_downloader';	// The extension key.
	// var $pi_checkCHash = true;
	var $filebasepath	 = "uploads/tx_sbdownloader/";
	// multilanguage
	var $langArr;
	var $sys_language_mode;

	var $cat_rows = array();
	var $config = array();
	var $mHooks = array();

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		global $TSFE,$LANG;
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
			
	   // Preconfigure the typolink
		$this->local_cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer");
		$this->local_cObj->setCurrentVal($GLOBALS["TSFE"]->id);
		$this->typolink_conf = $this->conf["typolink."];
		$this->typolink_conf["parameter."]["current"] = 1;
		$this->typolink_conf["additionalParams"] =
		$this->cObj->stdWrap($this->typolink_conf["additionalParams"],
		$this->typolink_conf["additionalParams."]);
		unset($this->typolink_conf["additionalParams."]);	
 
		$this->initHooks();
		
		$this->lang = $GLOBALS['TSFE']->config['config']['sys_language_uid'] ;
				
		// take objects from dedicated page or sysfolder
		$this->download = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('download');
		$this->did = intval(\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('did'));
		$this->pid = intval($this->piVars['uid']);
		$this->keyword = trim($this->piVars['sword']);
		$this->licenceAccepted = intval($this->piVars['licence']);
		$this->cat = intval($this->piVars['catid']);
		$this->subcats=$this->piVars['scat'];
		$slink=\TYPO3\CMS\Core\Utility\GeneralUtility::_GP('shortlink');
		if(isset($slink)) {
			$this->shortlink = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP('shortlink');
		}
		if(isset($this->piVars['shortlink'])){
			$this->shortlink = $this->piVars['shortlink'];
		}		
		// flexform Integration
		$this->pi_initPIflexform(); // Init and get the flexform data of the plugin

		// Template settings
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
		$this->templateCode = $this->cObj->fileResource($templateflex_file?'uploads/tx_sbdownloader/' .	 $templateflex_file:$this->conf['templateFile']);
		$this->mainCat = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dynField', 'sDEF');
		// Flexform data
		if(!empty($this->cat)) {
			$cat = $this->cat;
		}else{
			$cat = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'dynField', 'sDEF');			
		}

		// print_r($cat);
		$secureDownloads = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'secureDownloads', 's_conf');
		$this->showAll = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showAll', 's_conf');
		$filesize = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'filesize', 's_conf');
		$downloadcount = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'downloads', 's_conf');
		// Display Creationdate and -time !?    (KK)
		$showCRDate = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showCRDate', 's_conf');
		$showCRDate = trim($showCRDate) == '' ? $this->conf["displayCreationDate"] : $showCRDate;
		$showFiledate = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'filedate', 's_conf');
		$showFiledate = trim($showFiledate) == '' ? $this->conf["showFiledate"] : $showFiledate;
		$showEditDate = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showEditDate', 's_conf');
		$orderby = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderby', 's_conf');	
		$orderbyCats = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'orderbyCats', 's_conf');	
		$orderby = trim($orderby) == '' ? $this->conf["sortBy"] : $orderby;		
		$orderbyCats = trim($orderby) == '' ? $this->conf["sortByCats"] : $orderbyCats;		
		$imagelink = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'imagelink', 's_conf');	
		$imagelink = trim($imagelink) == '' ? $this->conf["imagelink"] : $imagelink;		
		$limit = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'limit', 's_conf');	
		$limit = trim($limit) == '' ? $this->conf["limit"] : $limit;		
		$ascdesc = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascdesc', 's_conf');
		$ascdescCats = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'ascdescCats', 's_conf');
		$onlyFirst = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'onlyFirst', 's_conf');
		$showMore = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'showMore', 's_conf');
		$this->showrelated = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'related', 's_conf');
		$singlePIDs = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'singlePID', 's_singlepid');
		$this->linkTitle = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkTitle', 's_conf');
		$this->licencePID = $this->conf['licencePID'];
		$this->loginpage = $this->conf['loginpage'];
		$this->licenceAcceptedPID = $this->conf['licenceAcceptedPID'];
		$this->tabID = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'tabid', 's_template');
		$this->tabID = intval($this->tabID);
		$this->heigth = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 's_template');
		
		$this->licence = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'licence', 's_licence');
		if(empty($this->licence)) $this->licence = 0;
		if($this->licence) $secureDownloads = 1;
		// print_r($this->licence);
		// check orderby records
		if($orderby == 'singleID'){
			$orderby = " FIELD(tx_sbdownloader_images.uid,$singlePIDs)";				
		}	
		if($orderby == 'backend'){
			$orderby = " tx_sbdownloader_images.sorting";				
		}	
		if(empty($orderby) || $orderby == "name"){
			$orderby = 'name';
			// $orderby = ' (ASCII(name) < 48 OR ASCII(name) > 57), name';
			// $orderby = ' CAST(`name` AS DECIMAL) ASC, CAST(`name` AS CHAR)';
			// $orderby = " name, LENGTH(name),'0'";
		}
		// check orderby categories
		if(empty($orderbyCats) || $orderbyCats == "cat"){
			$orderbyCats = 'cat';
		}		

		
		// print_r($orderbyCats);		
		// Copy Flexform data to $this->config
		$items=array('ascdesc','ascdescCats','cat','downloadcount','filesize','onlyFirst','orderby','orderbyCats','secureDownloads','showCRDate','showEditDate','showFiledate','showMore','singlePIDs','imagelink','limit');
		foreach($items as $item){
			$this->config[$item]=$$item;
		}
		// print_r($this->config);
		// added by Simon Schick
		// If no display is set in the Typo-Element it will look at the TypoScript
		if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF')) {
		  $view = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'what_to_display', 'sDEF');
		} else {
			$view = $this->conf["what_to_display"];
		}		

		// print_r($view);
		
		// set licencecookie
		if($this->licenceAccepted) {
			setcookie('sbdlicence', 'licence accepted', time()+172800); # 2 Tage gültig
		}
		// check if licence confirmed
		if($this->licence) {
			if(!$_COOKIE["sbdlicence"]) { 
				$checklicence = 1; 
				unset($this->download);	
			}else{				
				$checklicence = 0;	
			}
		}

			if(isset($this->download)) {
					$this->download = $this->getLinkName($this->download,$this->did);
					
					// print_r($this->download);
					// exit;
					// $this->downloadImage(basename($this->download),$this->did);
					$this->downloadImage($this->download,$this->did);
					exit;	
				}		
		
		$where=array();

		// Search
		if(!empty($this->keyword)){
			$keyword='"%'.$GLOBALS['TYPO3_DB']->quoteStr($this->keyword,'tx_sbdownloader_images').'%"';
			$where[]='tx_sbdownloader_images.description LIKE '.$keyword;
			$where[]='tx_sbdownloader_images.name LIKE '.$keyword;
			$where[]='tx_sbdownloader_images.longdescription LIKE '.$keyword;
			$where[]='tx_sbdownloader_images.linkdescription LIKE '.$keyword;
			$where=array('('.implode(' OR ',$where).')');
			// cache deaktivieren
			$this->pi_USER_INT_obj = 1;	
		// }elseif($checklicence){
				// cache deaktivieren
				// $this->pi_USER_INT_obj = 1;				
		}else{
			// cache aktiviert
			$this->pi_USER_INT_obj = 0;	
		}
		// latest und search
		if(!\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('sb_downloader')){
			$whereLatest = $where;	
		}else{
			$whereLatest = array();
		}
		// if($checklicence){
		// cache deaktivieren
		// $this->pi_USER_INT_obj = 1;	
		// }
		// separate views
		$view = explode(',',$view);
		
		foreach($view as $mode){
			switch($mode){
				case 'LIST':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE###');
					$this->template['listItem'] = $this->cObj->getSubpart($this->template['template'],'###LIST_ITEM###');
					$content.=$this->getList($where);
				break;
				case 'SINGLE':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SINGLE###');
					$content.=$this->getSingle($this->pid);
				break;
				case 'SINGLEDOWNLOAD':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SINGLE###');
					$content.=$this->getSingle($this->pid,$mode);
					// $this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SINGLE_ID###');
					// $this->template['listItem'] = $this->cObj->getSubpart($this->template['template'],'###LIST_SINGLE_ITEM###');
					// $singlePIDs = $GLOBALS['TSFE']->fe_user->getKey('ses','did');
					// $where[]='tx_sbdownloader_images.uid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($singlePIDs).') AND deleted=0 AND hidden=0 ';
					// $this->conf['view'] = 'SINGLE_ID';
					// $content.=$this->getList($where);						
				break;				
				case 'SINGLE_ID':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SINGLE_ID###');
					$this->template['listItem'] = $this->cObj->getSubpart($this->template['template'],'###LIST_SINGLE_ITEM###');
					$where[]='tx_sbdownloader_images.uid IN ('.$GLOBALS['TYPO3_DB']->cleanIntList($singlePIDs).') AND deleted=0 AND hidden=0 ';
					$this->conf['view'] = 'SINGLE_ID';
					$content.=$this->getList($where);					
				break;
				case 'SEARCH':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_SEARCH###');
					$content.= $this->getSearch($this->keyword);
				break;
				case 'LATEST':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_LATEST###');
					$this->template['listItem'] = $this->cObj->getSubpart($this->template['template'],'###LIST_LATEST_ITEM###');					
					$content.=$this->getList($whereLatest,$mode);					
				break;
				case 'CATEGORY':
					if(empty($this->keyword)){
						$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_CAT###');	
						$content.=$this->getCat($this->config['cat']);
					}				
				break;	
				case 'SHORTLINK':
					$this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE###');
					$this->template['listItem'] = $this->cObj->getSubpart($this->template['template'],'###LIST_ITEM###');
						$where[]='tx_sbdownloader_images.shortlink="'.$this->shortlink.'" AND deleted=0 AND hidden=0 ';
					// $this->conf['view'] = 'SHORTLINK';
					$content.=$this->getList($where,"SHORTLINK");					
				break;				
				// case 'LICENCECHECK':										
						// $content.=$this->getLicence($checklicence);	
				// break;
				// case 'LICENCEACCEPTED':							
						// $content.=$this->licenceAccepted($checklicence);	
				break;					
			}
		}

	  return $content;
	} // function main

	/**
	 * get the linkname of download id
	 *
	 * @param	int	$download: id of row-field
	 * @param	int	$did: id of download record
	 * @return	The generated form
	 */		
	
	function getLinkName($download="",$did=""){
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('image','tx_sbdownloader_images','uid='.intval($did)); 
		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			// $row=$this->getOverlay('tx_sbdownloader_images',$row);		
			$images = explode(',',$row['image']);
			$i=0;
			// print_r($row);
			foreach ($images as $val) {
				if($i == $download){
					$out=$val;
				}
				$i++;
				// print_r($val);
			}
		}
		return $out;
	}
	
	function getCat($parentCat=""){		
		// if parentCat empty
		if(empty($parentCat)) return false;
		// if more than one category
		if(strpos($parentCat,",")!==false) return false;		
		// get cats  
		  $where="AND tx_sbdownloader_images_parent_cat_mm.uid_foreign='$parentCat'";
		  $orderby=$this->config['orderbyCats'].' '.$this->config['ascdescCats'];
		  // $orderby='';
		  // print_r($orderby);
		  
		  
			// $res=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_sbdownloader_cat.cat as catname,tx_sbdownloader_cat.uid as catuid','tx_sbdownloader_images_parent_cat_mm','tx_sbdownloader_cat','',$where.$this->cObj->enableFields('tx_sbdownloader_cat') ,'',$orderby,$limit);	

     
    // $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
     // echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;	 
		// process query
		$res=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('tx_sbdownloader_cat.cat as catname,tx_sbdownloader_cat.uid as catuid','tx_sbdownloader_cat','tx_sbdownloader_images_parent_cat_mm','',$where.$this->cObj->enableFields('tx_sbdownloader_cat') ,'',$orderby,$limit);		
			$pageID=$GLOBALS["TSFE"]->id;
			$subcount = 0;
			while($cat=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				if($this->conf["useCatIcons"]) {
					$catname = '<img src="'.$this->conf["catIcon"].'">&nbsp;&nbsp;'.$cat['catname'];
				}else {
					$catname = $cat['catname'];
				}				
				if(empty($this->subcats)){
					$subcats=$cat['catuid'];
				}else{
					$subcats=$this->subcats.'_'.$cat['catuid'];
				}
				// print_r($subcats);
				 $catlist .= '<div class="sb_download_flex_cat">'.$this->pi_linkToPage($catname,$pageID,'',array('tx_sbdownloader_pi1[catid]' => $cat['catuid'],'tx_sbdownloader_pi1[scat]' => $subcats)).'</div>';
				 $subcount = 1;
			}
			if(empty($subcats)) $subcats = $this->subcats;
			$markerArray['###BREADCRUMB###'] = $this->breadcrumb($this->mainCat,$subcats,$subcount);
			$markerArray['###CATLIST###'] = $catlist;
		$content = $this->cObj->substituteMarkerArrayCached($this->template['template'], array(), $markerArray);
		// print_r($content);
		return $content;
	}	
	
	function getMarkerArray($image_row,$cat_rows=array()){
		
		// print_r($image_row);
		
		if($this->config['imagelink']){			
			// $this->conf["image."]["imageLinkWrap"]["enable"] = 0;
			$this->conf["image."]["imageLinkWrap"] = 0;
			// print_r($this->conf['image.']);
		}
		// if preview image
		if(!empty($image_row['imagepreview'])) {
			$preview = $image_row['imagepreview'];
			$filepath = $this->filebasepath.$preview;
			$imageext = $this->checkMimeType($filepath);
			$img = $this->conf["image."];
			$img["file"] = $filepath;
			$showImage = $this->cObj->IMAGE($img);
						
		}else{
			// Check images, the first image or pdf file will be displayed as thumbnail
			$images = explode(',',$image_row['image']);
			foreach ($images as $val) {
				$filepath = $this->filebasepath.$val;
				$imageext = $this->checkMimeType($filepath);
				// allowed mime types
				$imagemimetypes = array(
					'image/gif',
					'image/jpeg',
					'image/png',
					'image/bmp',
					'image/tiff',
				);
				// if no preview image
				if(in_array($imageext,$imagemimetypes)) {
					$img = $this->conf["image."];
					$img["file"] = $filepath;
					$showImage = $this->cObj->cObjGetSingle('IMAGE',$img);
					
					break;
				}else{
					// check fileext
					$fileinfo = \TYPO3\CMS\Core\Utility\GeneralUtility::split_fileref($val);
					$fileExt=trim($fileinfo['fileext']);
					if($fileExt == "pdf") {
						$img = $this->conf["image."];
						$img["file"] = $filepath;
						$showImage = $this->cObj->cObjGetSingle('IMAGEIMAGEIMAGE',$img);
						
						break;
					}else{
						$showImage = '';
					}
				}
			} // foreach ($images as $val)
		} // if(!empty($image_row['imagepreview')) {
		if(empty($image_row['clicks'])) {
			$image_row['clicks'] = '0';
		}
		// print_r($showImage);
		if($this->config['imagelink']) {
			$markerArray['###IMAGE###'] = $this->generateImageLink($image_row,$showImage);
		}else{				
			$markerArray['###IMAGE###'] = $showImage;
		}

		// If no ID is set in TS it will chose the site itself
		$id = $this->conf["singlePID"] ? $this->conf["singlePID"] : $GLOBALS["TSFE"]->id;
		// singleID of
		$singleID = $image_row['uid'];
		$more = $this->pi_linkToPage($this->pi_getLL('more'),$id,'',array('tx_sbdownloader_pi1[uid]' => $singleID));
		if($this->config['showMore']){
			$markerArray['###MORE###'] = $more;
		}elseif(!$showMore && !empty($image_row['longdescription'])) {
			$markerArray['###MORE###'] = $more;
		}else{
			$markerArray['###MORE###'] = '';
		}
		
		// overviewPID of the site
		// If no ID is set in TS it will chose the site itself
		if($this->conf["overviewPID"] == '') $this->conf["overviewPID"] = $GLOBALS["TSFE"]->id;
		$markerArray['###BACK###'] = $this->pi_linkToPage($this->pi_getLL('back'), $this->conf["overviewPID"]);
		// if download title as link
		if($this->linkTitle) {
			$markerArray['###TITLE###'] = $this->generateDownloadLinks($image_row,1);
			$markerArray['###LINKS###'] = '';
			
		}else{
			$markerArray['###TITLE###'] = $image_row['name'];
			$markerArray['###LINKS###'] = $this->generateDownloadLinks($image_row);
		}
		if($this->showrelated){
			if(!empty($image_row['related'])){
			
				// print_r($image_row['related']);
				$relArray=explode(',',$image_row['related']);
				// print_r($relArray);
				foreach ($relArray as $relid) {
					$markerArray['###RELATED###']=$this->getSingle($relid);
				}
			}else{
				// get original record
				// print_r($image_row);
				$link_uid = ($image_row['_LOCALIZED_UID']) ? $image_row['_LOCALIZED_UID'] : $image_row['uid'];
				// print_r($link_uid);
				$markerArray['###RELATED###']='';
			}
			
		}else{
			$markerArray['###RELATED###']='';
		}
		$categories=array();
		foreach(explode(',',$image_row['cat']) as $cat_id){
			$categories[]=$cat_rows[$cat_id]['cat'];
		}
		$markerArray['###CATEGORY###'] = $categories ? $this->pi_getLL('catDescription').implode(', ',$categories) : '';
		if($this->config['downloadcount']){
			$markerArray['###CLICKS###'] = $image_row['clicks'].' '.$this->pi_getLL('downloads');
		}else{
			$markerArray['###CLICKS###'] = '';
		}

		// Display Creationdate and -time !?    (KK)
		// displayCreationDate (0 = no date & time, 1 = only date, 2 = date & time)
//							$showCRDate = 2;
		if($this->config['showCRDate'] == "1") {
			$markerArray['###DATE###'] = $this->pi_getLL('since') . '&nbsp;' . date($this->conf['dateformat'], $image_row['crdate']);
		}elseif($showCRDate == "2"){
			$markerArray['###DATE###'] = $this->pi_getLL('since') . '&nbsp;' . date($this->conf['dateformat'], $image_row['crdate']) . '&nbsp;' . date($this->conf['timeformat'], $image_row['crdate']) . '&nbsp;' . $this->pi_getLL('oclock');
		}	else{
			$markerArray['###DATE###'] = '';
		}
		// print_r($this->config['showEditDate']);
		if($this->config['showEditDate']) {
			$markerArray['###LASTEDIT###'] = $this->pi_getLL('lastedit') . '&nbsp;' . date($this->conf['dateformat'], $image_row['tstamp']);
		}else{
			$markerArray['###LASTEDIT###'] = '';
		}
		if(!empty($image_row['description'])){
			$markerArray['###SHORTDESCRIPTION###'] = nl2br($this->getTypoLink($image_row['description']));
		}else{
			$markerArray['###SHORTDESCRIPTION###'] = '';
		}

		if(!empty($image_row['longdescription'])){
			$markerArray['###DESCRIPTION###'] = nl2br($this->getTypoLink($image_row['longdescription']));
		}else{
			$markerArray['###DESCRIPTION###'] = '';
		}
		if(!empty($this->keyword)){
			$markerArray['###HEADLINE###'] = $this->pi_getLL('searchresult');
		}else{
			$markerArray['###HEADLINE###'] = "";
		}
		if(!empty($this->heigth)){
			$markerArray['###HEIGHT###'] = 'style="height:'.$this->heigth.'"';
		}else{
			$markerArray['###HEIGHT###'] = '';
		}
		return $markerArray;
	}

	
	/**
	 * Generates breadcrumb for category menu
	 *
	 * @param	int	$cat: id of main category
	 * @return	The generated form
	 */		
	function breadcrumb($cat,$subcats="",$subcount=""){
		// print_r($subcount);
		$subcats = explode("_",$subcats);
		if($subcount) {		
			$lastsub = array_pop($subcats);		
		}
		// you are here
		$out = $this->pi_getLL('breadcrumb');
		// maincat
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('cat','tx_sbdownloader_cat','uid='.intval($cat).$this->getWhere('tx_sbdownloader_cat')); 
		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			if(empty($subcats)) {
				$out .= $row['cat'];
			}else{
				$out .= $this->pi_linkToPage($row['cat'],$GLOBALS["TSFE"]->id);
			}
		}
		// print_r($subcats);
		//subcats
		if(!empty($subcats)) {	
			// get subcats
			foreach($subcats as $value) {
				$resSub=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,cat','tx_sbdownloader_cat','uid='.intval($value).$this->getWhere('tx_sbdownloader_cat')); 
				while($rowSub=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($resSub)){
					if($value != $this->cat) {
						$out .= ' > '.$this->pi_linkToPage($rowSub['cat'],$GLOBALS["TSFE"]->id,'',array('tx_sbdownloader_pi1[catid]' => $rowSub['uid'],'tx_sbdownloader_pi1[scat]' => $value));
					}else{						
						$out .= ' > '.$rowSub['cat'];
					}					
					
				}				
			}
		}
		return $out;
	}
	/**
	 * Generates the licenceAccepted form
	 *
	 * @param	string	$keyword: search word
	 * @return	The generated form
	 */	
	// function licenceAccepted($checklicence){
		// if($checklicence) {		
			// $download = t3lib_div::_GP('download');
			// $did = intval(t3lib_div::_GP('did'));
			// $pid = $this->conf['licencePID'];
			// $link = $this->pi_getPageLink($pid,'',$urlParameters=array('download' => $download, 'did' => $did, 'sid' => $pid));
			// $content = '<meta http-equiv="refresh" content="0; URL='.$link.'">';
			// $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = $content;
		// }else{
			// $this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_LICENCEACCEPTED###');
			// $markerArray['###DLHEADLINE###'] = $this->pi_getLL('dlheadline');
			// $content = $this->cObj->substituteMarkerArrayCached($this->template['template'], array(), $markerArray);
		// }
		// return $content;		
	// }
	
	
	/**
	 * Generates the search form
	 *
	 * @param	string	$keyword: search word
	 * @return	The generated form
	 */	
	// function getLicence($checklicence){
	// print_r($checklicence);
	// exit;
		// if(!$checklicence) {
			// $this->pi_USER_INT_obj = 1;	
			// $download = t3lib_div::_GP('download');
			// $did = intval(t3lib_div::_GP('did'));
			// $pid = $this->conf['licenceAcceptedPID'];
			// $link = $this->pi_getPageLink($pid,'',$urlParameters=array('download' => $download, 'did' => $did, 'sid' => $pid));
			// $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId] = '<meta http-equiv="refresh" content="30; URL=http://www.test.de">';
			// $content = '<meta http-equiv="refresh" content="30; URL=http://www.test.de">';
			// print_r($link);
			// return $content;
			// exit;
			// header("Location: $link");			
		// }else{
			// $download = t3lib_div::_GP('download');
			// $did = intval(t3lib_div::_GP('did'));
			// $pid = $this->conf['licenceAcceptedPID'];			
			// $pid = $GLOBALS["TSFE"]->id;			
			// $this->template['template'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_LICENCE###');		
			// print_r($download);
			// $markerArray['###HEADLINE###'] = $this->pi_getLL('licenceHeadline');
			// $markerArray['###SUBMIT###'] = $this->pi_getLL('accept');
			
			// $this->pi_linkTP($strDLI, $urlParameters=array('download' => $val, 'did' => $uid));
			// $markerArray['###PID###'] = $this->pi_getPageLink($pid,'',$urlParameters=array('download' => $download, 'did' => $did, 'sid' => $pid));
			// $markerArray['###DID###'] = '<input type="hidden" value="'.$download.'" name="download">';
			// $markerArray['###DID###'].= '<input type="hidden" value="'.$did.'" name="did">';
			// $markerArray['###DID###'].= '<input type="hidden" value="'.$pid.'" name="sid">';
			// $content = $this->cObj->substituteMarkerArrayCached($this->template['template'], array(), $markerArray);
		// }
		// return $content;
	// }
	
	
	function getList($where=array(),$mode=""){	
		// print_r($this->shortlink);
	
		// show records of choosen cats
		if($this->config['cat']) {
			$cat = explode(",",$this->config['cat']);
			foreach ($cat as $value){
				$catvalue[] = 'uid_foreign='.$value;
			}
			$cats='('.implode(' OR ',$catvalue).')';
			if(empty($this->keyword)) {
				$where[]=$cats;	
			}
			if(!empty($this->keyword) && !$this->showAll) {
				$where[]=$cats;	
			}
		}
		
		// set limit if mode "LATEST"
		if($mode=="LATEST"){
			$limit = $this->config['limit'];
			if(!empty($cats)){
				$where[]=$cats;
			}
		}
		if($mode=="SHORTLINK" && empty($this->shortlink)) {	
			return $this->pi_getLL('norecords');
		}else{
			// Fetch downloads (mm)
			$res=$GLOBALS['TYPO3_DB']->exec_SELECT_mm_query('DISTINCT tx_sbdownloader_images.*','tx_sbdownloader_images','tx_sbdownloader_images_cat_mm','',($where ? ' AND '.implode(' AND ',$where) : '').$this->getWhere('tx_sbdownloader_images'),'',$this->config['orderby'].' '.$this->config['ascdesc'],$limit);
			
		// $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
		 // echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;	 
			if(!empty($this->keyword) && $mode!="LATEST"){ 
				$headline = '<h2>'.$this->pi_getLL('searchresult').'</h2>'; 
				
			}
			if($GLOBALS['TYPO3_DB']->sql_num_rows($res) == 0) {
				if(!empty($this->keyword)) {
					$noresult = $this->pi_getLL('noresult');
				}else{
					$noresult = $this->pi_getLL('norecords');
				}
			}
			while($image_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){			
				$image_row=$this->getOverlay('tx_sbdownloader_images',$image_row);
				// print_r($image_row);
				if($image_row){
					$image_row=$this->getCategories($image_row);
					
					$markerArray=$this->getMarkerArray($image_row,$this->cat_rows);
					foreach($this->mHooks as $lHookObj) {
						if (method_exists($lHookObj, 'additionalListItemMarker')) {
							$markerArray = $lHookObj->additionalListItemMarker($markerArray,$image_row,$this);
						}
					}
					$content_item .= $this->cObj->substituteMarkerArrayCached($this->template['listItem'], $markerArray);
				}
			}

			$subpartArray['###CONTENT###'] = $headline.$content_item.$noresult;
			return $this->cObj->substituteMarkerArrayCached($this->template['template'], $markerArray, $subpartArray);
		}
	}

	/**
	 * Generates the search form
	 *
	 * @param	string	$keyword: search word
	 * @return	The generated form
	 */	
	function getSearch($keyword){
		$markerArray['###HEADLINE###'] = $this->pi_getLL('searchHeadline');
		$markerArray['###SUBMIT###'] = $this->pi_getLL('submit');
		$markerArray['###KEYWORD###'] = $keyword;
		// $markerArray['###TABID###'] = $this->tabID;
		// print_r($this->tabID);
		if(!empty($this->tabID)){
			$getVars = array('&tx_sbtab_pi1[tab]' => $this->tabID);
		}else{
			$getVars = '';
		}
		$markerArray['###SEARCHPID###'] = $this->pi_getPageLink($GLOBALS["TSFE"]->id,'',$getVars);
		$content = $this->cObj->substituteMarkerArrayCached($this->template['template'], array(), $markerArray);
		return $content;
	}

	/**
	 * Display only single items
	 *
	 * @param	int	$id: id of download
	 * @return	The generated item
	 */	
	function getSingle($id,$mode=""){
	
	// print_r($id);
	// print_r($GLOBALS['TSFE']->fe_user->getKey('ses','did'));
	if($mode == 'SINGLEDOWNLOAD') {
		$id = $GLOBALS['TSFE']->fe_user->getKey('ses','did');
	}
	// if($mode="REL") {
		// $where = 'uid IN ('.$id.')';
		// $where = 'uid='.intval($id);
	// }else{
		$where = 'uid='.intval($id);
	// }	
		// Fetch download
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_sbdownloader_images',$where.$this->getWhere('tx_sbdownloader_images')); 
		if($image_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$image_row=$this->getOverlay('tx_sbdownloader_images',$image_row);

			if($image_row){
				$image_row=$this->getCategories($image_row);
				$markerArray=$this->getMarkerArray($image_row,$this->cat_rows);
				foreach($this->mHooks as $lHookObj) {
					if (method_exists($lHookObj, 'additionalItemMarker')) {
						$markerArray = $lHookObj->additionalItemMarker($markerArray, $this);
					}
				}
				return $this->cObj->substituteMarkerArrayCached($this->template['template'], array(), $markerArray);
			}
		}
	}

	function getCategories($image_row){
		$cat_ids=array();
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign','tx_sbdownloader_images_cat_mm','uid_local='.$image_row['uid'],'','sorting');
		while($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$cat_ids[]=$row['uid_foreign'];
			// Fetch categories
			if(!$this->cat_rows[$row['uid_foreign']]){
				$res2=$GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_sbdownloader_cat','uid='.$row['uid_foreign'].$this->getWhere('tx_sbdownloader_cat'));
				if($cat_row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res2)){
					$cat_row=$this->getOverlay('tx_sbdownloader_cat',$cat_row);
					if($cat_row){
						$this->cat_rows[$row['uid_foreign']]=$cat_row;
					}
				}
			}
		}
		$image_row['cat']=implode(',',$cat_ids);
		return $image_row;
	}

	
	function getWhere($table){
		// Enable fields
		// print_r($this->conf);
		$where=$this->cObj->enableFields($table);	
		// Translation if no single_id
		if($this->conf['view'] == 'SINGLE_ID'){
			return false;
		}else{
		$ctrl=$GLOBALS['TCA'][$table]['ctrl'];

			$where.=' AND ('.$table.'.'.$ctrl['languageField'].' IN (-1,0)';
			if($GLOBALS['TSFE']->sys_language_content && $ctrl['transOrigPointerField']){
				$where.=' OR ('.$table.'.'.$ctrl['languageField'].'='.intval($GLOBALS['TSFE']->sys_language_content).' AND '.$table.'.'.$ctrl['transOrigPointerField'].'=0)';
			}
			$where.=')';
		
		// Version
		$where.=' AND '.$table.'.pid>=0';	
		return $where;
		}
	}
	
	/**
	 * Get language overlay
	 *
	 * @param	int		$table: database table
	 * @param	array	$row: query of original records
	 * @return	The overlay language
	 */
	function getOverlay($table,$row){
		if(is_array($row)){
			$ctrl=$GLOBALS['TCA'][$table]['ctrl'];

			// Version
			// - Table has versioning
			// - Current user is in workspace
			// - Versioning is enabled
			if($ctrl['versioningWS'] && $GLOBALS['BE_USER']->workspace && t3lib_extMgm::isLoaded('version')){
				$GLOBALS['TSFE']->sys_page->versionOL($table,$row);
			}

			// Translation
			// - Table has translation
			// - Current language is not default
			// - Translation is enabled
			if($ctrl['languageField'] && $GLOBALS['TSFE']->sys_language_content) {
				// print_r(1);
				// exit;
				$row=$GLOBALS['TSFE']->sys_page->getRecordOverlay($table,$row,$GLOBALS['TSFE']->sys_language_content,$GLOBALS['TSFE']->sys_language_contentOL);
			}
 		}
 		return $row;
 	}
	
	
	/**
	 * Generates the download links
	 *
	 * @param	int		$uid: The download uid
	 * @param	array		$downloaddescription:1 = filename.fileextension, 2 = filename, 3 = fileextension
	 * @param	array		$downloadIcon: which downloadicon
	 * @param	array		$filesize: show filesize (true/false)
	 * @param array		$mode: values (all=all information, icon=only icons, size=only filesize, links=only links)
	 * @return	The generated links
	 */
	function generateDownloadLinks($row,$linkTitle="0"){	
	
		// print_r($row);
		// get correct language uid for translated realurl link		
		$link_uid = ($row['_LOCALIZED_UID']) ? $row['_LOCALIZED_UID'] : $row['uid'];
		// Template settings
		$templateflex_file = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'template_file', 's_template');
		$this->templateCode = $this->cObj->fileResource($templateflex_file?'uploads/tx_sbdownloader/' .	 $templateflex_file:$this->conf['templateFile']);
		$template['templateLinks'] = $this->cObj->getSubpart($this->templateCode,'###TEMPLATE_LINKS###');
		$template['linkItem'] = $this->cObj->getSubpart($template['templateLinks'],'###LINK_ITEM###');

		// explode link description
		$description = explode('<br />',nl2br($row['downloaddescription']));
		$linkdescription = explode('<br />',nl2br($row['linkdescription']));
		$i = 0;

		if(!empty($row['image'])){
			// explode images
			$images = explode(',',$row['image']);
			
			foreach ($images as $val) {
				// break loop if only first record should be displayed
				if($this->config['onlyFirst'] && $i == 1) break;
				$markerArray=$this->generateDownloadLink($link_uid,$val,$description[$i],$linkdescription[$i],'','',$row['name'],$row['tstamp'],$i);
				$link_item .= $this->cObj->substituteMarkerArrayCached($template['linkItem'], $markerArray);
				$i++;
			} //foreach ($images as $val) {
		}

		if(!empty($row['externallinks'])){
			// External links
			$external = explode(',',$row['externallinks']);
			foreach ($external as $key=>$val) {
				if($this->config['onlyFirst'] && $i == 1) break;
				$markerArray=$this->generateDownloadLink($link_uid,$val,$description[$i],$linkdescription[$i],$key+1,'',$row['name'],$row['tstamp'],$i);
				$link_item .= $this->cObj->substituteMarkerArrayCached($template['linkItem'], $markerArray);
				$i++;
			}
		}

		$subpartArray['###CONTENT_LINK###'] = $link_item;
		$content .= $this->cObj->substituteMarkerArrayCached($template['templateLinks'], $markerArray, $subpartArray);

		return $content;
	}

	function generateImageLink($row,$filepath) {
		$i = 0;
		// get correct language uid for translated realurl link		
		$link_uid = ($row['_LOCALIZED_UID']) ? $row['_LOCALIZED_UID'] : $row['uid'];
		if(!empty($row['image'])){
			// explode images
			$images = explode(',',$row['image']);
			foreach ($images as $val) {
				// only first file
				if($i == 1) break;
				$markerArray=$this->generateDownloadLink($link_uid,$val,$description[$i],$linkdescription[$i],0,$filepath,$row['name'],$row['tstamp'],$i);
				$i++;
			} //foreach ($images as $val) {			
		}	
		if(!empty($row['externallinks'])){
			// External links
			$external = explode(',',$row['externallinks']);
			foreach ($external as $key=>$val) {
				if($i == 1) break;
				$markerArray=$this->generateDownloadLink($link_uid,$val,$description[$i],$linkdescription[$i],$key+1,$filepath,$row['name'],$row['tstamp'],$i);
				$i++;
			}
		}				
		// print_r($markerArray);
		return $markerArray;
	}
	
	function generateDownloadLink($uid,$val,$description,$linkdescription,$external=false,$img="",$name,$crdate="",$i=""){
		// print_r($external);
		$markerArray['###ICON###'] = '';
		$markerArray['###LINK###'] = '';
		$markerArray['###FILESIZE###'] = '';
		$markerArray['###MODIFICATIONDATE###'] = '';
		$markerArray['###LINKDESCRIPTION###'] = '';
		$markerArray['###LASTEDIT###'] = '';
		$fileinfo = \TYPO3\CMS\Core\Utility\GeneralUtility::split_fileref($val);

		
		// print_r($description);
		
		// read out link descriptions
		if(!empty($linkdescription)){
			$markerArray['###LINKDESCRIPTION###'] = trim($this->getTypoLink($linkdescription));
		}
		if(!empty($crdate)) {
			if($this->config['showEditDate']) {
				$markerArray['###LASTEDIT###'] = $this->pi_getLL('lastedit') . '&nbsp;' . date($this->conf['dateformat'], $crdate);
			}else{
				$markerArray['###LASTEDIT###'] = '';
			}
		}

		// no description given
		// print_r($this->conf['forcelinkdescription']);
		if($this->conf['forcelinkdescription']){
			// check typoscript settings
			switch($this->conf["linkdescription"]){
				case 1:
					$fileName=trim($fileinfo['filebody']).'.'.trim($fileinfo['fileext']);
					break;
				case 2:
					$fileName=trim($fileinfo['filebody']);
					break;
				case 3:
					$fileName=trim($fileinfo['fileext']);
					break;
			}
		// description given
		}elseif($this->linkTitle) {
			$fileName = $name;
		}else{
			$fileName=trim($description);
		}

		// Render Downloadicon        (KK)
		$strDLI = '';
		if(!empty($this->conf["downloadIcon"])){
			// Link params
			$params = array(
				'title' => $this->pi_getLL('linkTitle'),
				'rel' => 'nofollow'
			);
			// print_r($this->conf['linkTarget']);
			if ($this->conf['linkTarget'] != '') {
				$params['target'] = $this->conf['linkTarget'];
			}

			if (substr($this->conf["downloadIcon"],-1)=='/') {     	// so the last letter is a Slash!
				// now we take the corresponding GIFs for the different file-extensions,
				// normally in folder "typo3/gfx/fileicons/"
				// if file icon exist
				if(file_exists($this->conf["downloadIcon"].trim($fileinfo['fileext'].'.gif'))){					
					$strDLI = '<img src="'.$this->conf["downloadIcon"].trim($fileinfo['fileext']).'.gif" width="18" height="16">';
				}elseif(file_exists('typo3/gfx/fileicons/'.trim($fileinfo['fileext'].'.gif'))){
					$strDLI = '<img src="typo3/gfx/fileicons/'.trim($fileinfo['fileext']).'.gif" width="18" height="16">';				
				}else{
					$strDLI = '<img src="'.$this->conf["missingDownloadIcon"].'">';
				}
			} else {
				$strDLI = '<img src="'.$this->conf["downloadIcon"].'">';
			}

			// licence check
			if($this->licence) {
				$addPid = $this->licencePID;
			}else{
				$addPid = '';
			}			
			// print_r($this->licencePID);
			// print_r($this->licence);
			//secureDownloads						
			if($this->config['secureDownloads']){
				$icon = $this->pi_linkTP($strDLI, $urlParameters=array('download' => $i, 'did' => $uid),'',$addPid);
				$markerArray['###ICON###'] = $this->cObj->addParams($icon,$params);
			}else{
				if(!$external) {
					$file=$this->filebasepath.$val;				
				}else{
					$file=$val;				
				}
				$markerArray['###ICON###'] = $this->pi_linkToPage($strDLI,$file,$this->conf['linkTarget']);
			}
		}else{
			$markerArray['###ICON###'] = '';
		}
		// if empty filename
		if(empty($fileName)){
			$fileName = $val;
		}

		// build link via typolink
		$temp_conf = $this->typolink_conf;
		$temp_conf["section"] .= '';
		$temp_conf['target']=$this->conf['linkTarget'];
		//secureDownloads
		// print_r($external);
		if($this->config['secureDownloads'] && !$external){
		// if($this->config['secureDownloads']){
			$temp_conf["additionalParams"] .= '&no_cache=1&download='.($external ? $external : $i).'&did='.$uid;
			$temp_conf["addQueryString"] = true;                                          
			$temp_conf["useCacheHash"] = false;
		}else{
			unset($temp_conf['parameter.']);
			$temp_conf['parameter']=($external ? '' : $this->filebasepath).$val;
		}
		$link = $this->local_cObj->typolink($fileName, $temp_conf);
		
		$markerArray['###LINK###'] = $this->cObj->addParams($link,$params);

		// filesize
		if($this->config['filesize'] && !$external){
			$downloadfile = $this->filebasepath.$val;
			$valfilesize = filesize($downloadfile);
			$valfilesize = $this->format_size($valfilesize);
			$markerArray['###FILESIZE###'] = ' '.$this->pi_getLL('bracketstart').$this->pi_getLL('filesize').$valfilesize.$this->pi_getLL('bracketend');
		}
		// file modification date
		if($this->config['showFiledate'] && !$external){
			$downloadfile = $this->filebasepath.$val;
			$filemtime = filemtime($downloadfile);
			$dateTimeFormat = $this->conf['datetimeformat'];
			if ($dateTimeFormat == '' || empty($dateTimeFormat)) $dateTimeFormat = "d.m.Y H:i";
			$strFilemtime = date($dateTimeFormat, $filemtime);
			$mdsc = trim($this->conf['fileMDateClass']);
			$markerArray['###MODIFICATIONDATE###'] = $this->pi_getLL('fileDate').$strFilemtime;
		}
		
		if(!empty($img)) {			
			if($this->config['secureDownloads']){			
				// print_r($addPid);
				$imglink = $this->pi_linkTP($img, $urlParameters=array('download' => $i, 'did' => $uid, 'sid'=>$GLOBALS["TSFE"]->id),'',$addPid);
				$imagelink = $this->cObj->addParams($imglink,$params);
			}else{
				$file=$this->filebasepath.$val;				
				$imagelink = $this->pi_linkToPage($img,$file,$this->conf['linkTarget']);
			}
			return $imagelink;
		}else{		
			return $markerArray;
		}
	}

	/**
	 * Format filesize
	 *
	 * @param	int		$size: size of file in bytes
	 * @param	array		$round filesize: true/false
	 * @return	return formated filesize
	 */
	function format_size($size, $round = 0) {
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i=0; $size > 1024 && $i < count($sizes) - 1; $i++) $size /= 1024;
    return round($size,$round).$sizes[$i];
}

	/**
	 * checks mime_type of an image
	 *
	 * @param	int		$file: filename
	 * @return	mimetype
	 */
	function checkMimeType($file){
			$imageinfos=getimagesize($file);			 // read image info
			$imagetype=$imageinfos[2];                     // image-type
			$mimetype=image_type_to_mime_type($imagetype); // mime-type
			return $mimetype;
	}

	/**
	 * Download the file
	 *
	 * @param	string		$image: Name of download
	 * @param	array		$uid: download uid for click counter
	 */
	function downloadImage($image,$uid){
		// Check if user logged in
		$id = $GLOBALS['TSFE']->loginUser;
		// check if download only for logged in users
		$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('usercheck','tx_sbdownloader_images','uid='.intval($uid));
		if($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$check = $row['usercheck'];
		}
		if($id>0) $check=0;
		// if user logged in or no login necessary
		if ($check == 0){			
		// update counter
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_sbdownloader_images',
			'uid='.$uid,
			array(
				'clicks' => 'clicks+1',
			),
			array('clicks')
		);		
			$downloadarray = array('did'=>$uid, 'dtitle'=>$image);
      ob_start();
      // Here a hook for stats collection
			// Methode to implement in hook object
			// $lHook->saveStats($uid, tx_sbdownloader_pi1 &$pPiRef);           
			foreach($this->mHooks as $lHookObj) {
				if (method_exists($lHookObj, 'saveStats')) {
					$lHookObj->saveStats($this,$downloadarray);
          // exit;
				}
			}
      ob_end_clean();
		if(is_numeric($image)){
			$res=$GLOBALS['TYPO3_DB']->exec_SELECTquery('externallinks','tx_sbdownloader_images','uid='.intval($uid));
			if($row=$GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$external = empty($row['externallinks']) ? array() : explode(',',$row['externallinks']);
				if($external[$image-1]){
					header('Location: '.t3lib_div::locationHeaderUrl($external[$image-1]));
					header('X-Note: Redirect by sb_downloader');
					header('Connection: close');
				}
			}
		}else{
			$downloadfile = $this->filebasepath.$image;
			$filesize = filesize($downloadfile);
			$filename = $image;

			// check Mimetype
			$mimetype = $this->checkMimeType($downloadfile);
			header("Content-Type: $mimetype");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Length: $filesize");			
			// readfile($downloadfile);
			$this->readfile_chunked($downloadfile);			
			ob_start();
			// Here a hook for additional Smarty Vars
			// Methode to implement in hook object
			// $lHook->downloadCompleted($uid, tx_sbdownloader_pi1 &$pPiRef);
			foreach($this->mHooks as $lHookObj) {
				if (method_exists($lHookObj, 'downloadCompleted')) {
					$lHookObj->downloadCompleted($uid, $this);
				}
			}
			ob_end_clean();
			exit;
		}
		// login / registration page
		}else{
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'download', $image); 
			$GLOBALS['TSFE']->fe_user->setKey('ses', 'did', $uid); 
			$GLOBALS["TSFE"]->storeSessionData();
			$link = $this->pi_getPageLink($this->loginpage, '', $vars);
			$link = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($link); 
			header('Location: '.$link); 
			exit();			
		}
	} // function downloadImage

	protected function initHooks(){
		// First user defined objects (if any) for hooks which extend some functionality:
		$this->mHooks = array();
		//t3lib_div::debug($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]);
    // print_r($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]);
		if (is_array ($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey])) {
			foreach ($GLOBALS ['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey]['hook'] as $classRef) {
				$this->mHooks[] = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($classRef);		
			}
		}
	}// function initHooks

	/**
	 * Generate links from typolink syntax
	 *
	 * @param	string		$str: string to be parsed
	 */
	 
	 
    // Fuktion readfile_chunked();
    function readfile_chunked($filename) {
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            print $buffer;
            ob_flush();
            flush();
        }
        return fclose($handle);
    }	 

  function getTypoLink($str){
    // no p-tags in link
    $parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
    $parseFunc['nonTypoTagStdWrap.']['encapsLines.']['removeWrapping'] = 1;
    // remove &nbsp;
    $parseFunc['nonTypoTagStdWrap.']['encapsLines.']['innerStdWrap_all.']['ifBlank'] = '';
    $out = $this->cObj->parseFunc($str, $parseFunc);  
    return $out;    
  }

} // class end


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/pi1/class.tx_sbdownloader_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/pi1/class.tx_sbdownloader_pi1.php']);
}

?>
