<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Samuel Weiss (sw@rineco.ch)
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
/**
 * This function displays a selector with nested units.
 * The original code is borrowed from the extension "Digital Asset Management" (tx_dam) author: René Fritz <r.fritz@colorcube.de>
 *
* @author Rupert Germann <rupi@gmx.li> modified by Samuel Weiss
 * @package TYPO3
 * @subpackage sb_downloader
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   60: class tx_sbdownloader_tceFunc_selectTreeView extends t3lib_treeview
 *   73:     function wrapTitle($title,$v)
 *   95:     function getDataInit($parentId)
 *
 *
 *  156: class tx_sbdownloader_treeview
 *  166:     function displayHierarchyTree($PA, $fobj)
 *  426:     function getNotSelectableItems($PA,$parentId,$SPaddWhere)
 *
 *    SECTION: This function checks if there are units selectable that are not allowed for this BE user and if the current record has
 *  526:     function findRecursiveUnits ($PA,$row,$table,$storagePid,$treeIds)
 *  567:     function compareCategoryVals ($treeIds,$unitString)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


//if (!class_exists('t3lib_treeview')) require_once(PATH_tslib . 'class.t3lib_treeview.php');
	/**
	 * extend class t3lib_treeview to change function wrapTitle().
	 *
	 */
class tx_sbdownloader_tceFunc_selectTreeView extends \TYPO3\CMS\Backend\Tree\View\AbstractTreeView {

	var $TCEforms_itemFormElName='';
	var $TCEforms_nonSelectableItemsArray=array();
	var $table_MM='';

	/**
	 * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
	 *
	 * @param	string		$title: the title
	 * @param	array		$v: an array with uid and title of the current item.
	 * @param int $bank Bank pointer (which mount point number)
	 * @return	string		the wrapped title
	 */
	public function wrapTitle($title,$v,$bank = 0)	{
		if($v['uid']>0) {
			if (in_array($v['uid'],$this->TCEforms_nonSelectableItemsArray)) {
				return '<a href="#" title="'.$v['cat'].'"><span style="color:#999;cursor:default;">'.$v['cat'].'</span></a>';
			} else {
				$hrefTitle = $v['cat'];
				$aOnClick = 'setFormValueFromBrowseWin(\''.$this->TCEforms_itemFormElName.'\','.$v['uid'].',\''.$v['cat'].'\'); return false;';
				return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'" title="'.htmlentities($v['cat']).'">'.$v['cat'].'</a>';
			}
		} else {
			return $title;
		}
	}
	
	
/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		HTML-code prefix for recursive calls.
	 * @param	string		? (internal)
	 * @param	string		CSS class to use for <td> sub-elements
	 * @return	integer		The count of items on the level
	 */
	function getTree($uid, $depth = 999, $depthData = '', $blankLineCode = '', $subCSSclass = '') {
		
		// print_r($uid);
		// exit;
		// $uid = '1';
		
			// Buffer for id hierarchy is reset:
		$this->buffer_idH = array();

			// Init vars
		$depth = intval($depth);
		$HTML = '';
		$a = 0;

		$res = $this->getDataInit($uid, $subCSSclass);
		$c = $this->getDataCount($res);
		$crazyRecursionLimiter = 999;

		$idH = array();
		    // $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
     // echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
	 // exit;
			// Traverse the records:
		while ($crazyRecursionLimiter > 0 && $row = $this->getDataNext($res, $subCSSclass)) {
			// $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = 1;
			// echo $GLOBALS['TYPO3_DB']->debug_lastBuiltQuery;
			$a++;
			$crazyRecursionLimiter--;
			// print_r($row);
			$newID = $row['uid'];

			if ($newID == 0) {
				throw new RuntimeException('Endless recursion detected: TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of ' . $this->table . ':0 to a new value.<br /><br />See <a href="http://bugs.typo3.org/view.php?id=3495" target="_blank">bugs.typo3.org/view.php?id=3495</a> to get more information about a possible cause.', 1294586383);
			}

			$this->tree[] = array(); // Reserve space.
			end($this->tree);
			$treeKey = key($this->tree); // Get the key for this space
			$LN = ($a == $c) ? 'blank' : 'line';

				// If records should be accumulated, do so
			if ($this->setRecs) {
				$this->recs[$row['uid']] = $row;
			}

				// Accumulate the id of the element in the internal arrays
			$this->ids[] = $idH[$row['uid']]['uid'] = $row['uid'];
			$this->ids_hierarchy[$depth][] = $row['uid'];
			$this->orig_ids_hierarchy[$depth][] = $row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid'];

				// Make a recursive call to the next level
			$HTML_depthData = $depthData . ' <img' . \TYPO3\CMS\Backend\Utility\IconUtility::skinImg($this->backPath, 'gfx/ol/' . $LN . '.gif', 'width="18" height="16"') . ' alt="" />';
			if ($depth > 1 && $this->expandNext($newID) && !$row['php_tree_stop']) {
				$nextCount = $this->getTree(
					$newID,
					$depth - 1,
					$this->makeHTML ? $HTML_depthData : '',
					$blankLineCode . ',' . $LN,
					$row['_SUBCSSCLASS']
				);
				if (count($this->buffer_idH)) {
					$idH[$row['uid']]['subrow'] = $this->buffer_idH;
				}
				$exp = 1; // Set "did expand" flag
			} else {
				$nextCount = $this->getCount($newID);
				$exp = 0; // Clear "did expand" flag
			}

				// Set HTML-icons, if any:
			if ($this->makeHTML) {
				$HTML = $depthData . $this->PMicon($row, $a, $c, $nextCount, $exp);
				$HTML .= $this->wrapStop($this->getIcon($row), $row);
				#	$HTML.=$this->wrapStop($this->wrapIcon($this->getIcon($row),$row),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = array(
				'row' => $row,
				'HTML' => $HTML,
				'HTML_depthData' => $this->makeHTML == 2 ? $HTML_depthData : '',
				'invertedDepth' => $depth,
				'blankLineCode' => $blankLineCode,
				'bank' => $this->bank
			);
		}

		$this->getDataFree($res);
		$this->buffer_idH = $idH;
		// print_r($c);
		// Exit;
		return $c;
	}

	
		/**
	 * Getting the tree data: Selecting/Initializing data pointer to items for a certain parent id.
	 * For tables: This will make a database query to select all children to "parent"
	 * For arrays: This will return key to the ->dataLookup array
	 *
	 * @param	integer		parent item id
	 * @return	mixed		data handle (Tables: An sql-resource, arrays: A parentId integer. -1 is returned if there were NO subLevel.)
	 * @access private
	 */
	function getDataInit($parentId) {
		// sorting of cats
		$this->orderByFields = 'cat';
	
		// print_r($this->clause);
		// exit;
	
		if (is_array($this->data)) {
			if (!is_array($this->dataLookup[$parentId][$this->subLevelID])) {
				$parentId = -1;
			} else {
				reset($this->dataLookup[$parentId][$this->subLevelID]);
			}
			return $parentId;
		} else {
			if ($parentId>0){
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT '.
							implode(',',$this->fieldArray),
							$this->table.','.$this->table_MM, 'tx_sbdownloader_cat.uid = ' .$this->table_MM.'.uid_local AND ' .$this->table_MM.'.uid_foreign = '.$parentId.
								' AND l18n_parent=0 '.
								\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($this->table).
								$this->clause,	// whereClauseMightContainGroupOrderBy
							'',
							$this->orderByFields
						);
			} else {
				$restemp = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT '.
							implode(',',$this->fieldArray),
							$this->table.','.$this->table_MM, 'tx_sbdownloader_cat.uid = ' .$this->table_MM.'.uid_local'.' AND l18n_parent=0'.
								\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($this->table).
								$this->clause,	// whereClauseMightContainGroupOrderBy
							'',
							$this->orderByFields
						);
				// while ($line = mysql_fetch_array($restemp)){
				while ($line = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($restemp)){
				  $tempa[]=$line['uid'];
				}
				// mysql_free_result($restemp); <-- BAD. Better:
				$GLOBALS['TYPO3_DB']->sql_free_result($restemp);
				if ($tempa[0]){
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT '.
								implode(',',$this->fieldArray),
								$this->table, 'tx_sbdownloader_cat.uid not in ('.implode(",",$tempa).') '.' AND l18n_parent=0  AND parent_cat=0'.
									\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($this->table).
									$this->clause,	// whereClauseMightContainGroupOrderBy
								'',
								$this->orderByFields
							);
				} else {
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT '.
								implode(',',$this->fieldArray),
								$this->table, 'l18n_parent=0 AND parent_cat=0'.
									\TYPO3\CMS\Backend\Utility\BackendUtility::deleteClause($this->table).
									$this->clause,	// whereClauseMightContainGroupOrderBy
								'',
								$this->orderByFields
							);
				}
			}
			return $res;
			// print_r($res);
		}
		
		// print_r($res);
		// exit; 
	}
}

	/**
	 * this class displays a tree selector with nested sb_downloader units.
	 *
	 */
class tx_sbdownloader_treeview {

	/**
	 * Generation of TCEform elements of the type "select"
	 * This will render a selector box element, or possibly a special construction with two selector boxes. That depends on configuration.
	 *
	 * @param	array		$PA: the parameter array for the current field
	 * @param	object		$fobj: Reference to the parent object
	 * @return	string		the HTML code for the field
	 */
	function displayHierarchyTree($PA, $fobj)    {
		
		
		 
		
		// check if $PA['itemFormElValue'] empty
		// if(empty($PA['itemFormElValue'])){
			// get categories 
		// }
		
		$table = $PA['table'];
		$field = $PA['field'];
		$row = $PA['row'];

		$this->pObj = &$PA['pObj'];

			// Field configuration from TCA:
		$config = $PA['fieldConf']['config'];
				
			// it seems TCE has a bug and do not work correctly with '1'
		$config['maxitems'] = ($config['maxitems']==2) ? 1 : $config['maxitems'];
		

			// Getting the selector box items from the system
		///$selItems = $this->pObj->addSelectOptionsToItemArray($this->pObj->initItemArray($PA['fieldConf']),$PA['fieldConf'],$this->pObj->setTSconfig($table,$row),$field);
		//$selItems = $this->pObj->addItems($selItems,$PA['fieldTSConfig']['addItems.']);
		if ($config['itemsProcFunc']) 
			$selItems = $this->pObj->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);
 
			// Possibly remove some items:
		$removeItems=\TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);
		foreach($selItems as $tk => $p)	{
			if (in_array($p[1],$removeItems))	{
				unset($selItems[$tk]);
			} else if (isset($PA['fieldTSConfig']['altLabels.'][$p[1]])) {
				$selItems[$tk][0]=$this->pObj->sL($PA['fieldTSConfig']['altLabels.'][$p[1]]);
			}

				// Removing doktypes with no access:
			if ($table.'.'.$field == 'pages.doktype')	{
				if (!($GLOBALS['BE_USER']->isAdmin() || \TYPO3\CMS\Core\Utility\GeneralUtility::inList($GLOBALS['BE_USER']->groupData['pagetypes_select'],$p[1])))	{
					unset($selItems[$tk]);
				}
			}
		}
	
			// Creating the label for the "No Matching Value" entry.
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->pObj->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$this->pObj->getLL('l_noMatchingValue').' ]';
		$nMV_label = @sprintf($nMV_label, $PA['itemFormElValue']);


			// Prepare some values:
		$maxitems = intval($config['maxitems']);
		$minitems = intval($config['minitems']);
		$size = intval($config['size']);
			// If a SINGLE selector box...
		if ($maxitems<=1 AND !$config['treeView'])	{

		} else {
			if ($row['sys_language_uid'] && $row['l18n_parent'] ) { // the current record is a translation of another record
				if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sb_downloader']) { // get sb_downloader extConf array
					$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sb_downloader']);
				}
				// if ($confArr['useStoragePid']) {
					// $TSconfig = t3lib_BEfunc::getTCEFORM_TSconfig($table,$row);
					// $storagePid = $TSconfig['_STORAGE_PID']?$TSconfig['_STORAGE_PID']:0;
					
					// check if record storage pid exists
					$TSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getTCEFORM_TSconfig($table,$row);					
					if(!empty($TSconfig['_THIS_ROW']['pages'])){
						$sPid = $TSconfig['_THIS_ROW']['pages'];
						$sPids=explode(",",$sPid);
						foreach($sPids as $value) {
							$sValue = explode("|",$value);
							$sVal = explode("_",$sValue[0]);
							$storagePid[] = $sVal[1];
						}						
						$storagePid = implode(',',$storagePid);
					}else{						
						$storagePid = $TSconfig['_STORAGE_PID']?$TSconfig['_STORAGE_PID']:0; 
					}					

					// t3lib_div::debug($TSconfig);
					// exit;
				if(!empty($storagePid)){
					$SPaddWhere = ' AND tx_sbdownloader_cat.pid IN (' . $storagePid . ')';
				}
// t3lib_div::debug(t3lib_BEfunc::getTCEFORM_TSconfig($table,$row));
				$errorMsg = array();
//				$notAllowedItems = array();
//				if ($GLOBALS['BE_USER']->getTSConfigVal('options.useListOfAllowedItems') && !$GLOBALS['BE_USER']->isAdmin()) {
//					$notAllowedItems = $this->getNotAllowedItems($PA,$SPaddWhere);
//				}
					// get unit of the translation original 
					// $SPaddWhere = ' AND tx_sbdownloader_cat.pid IN ("356")';
				if(!empty($this->table_MM)){
					$unitres = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('uid,cat,sorting AS mmsorting', 'tx_sbdownloader_cat'.$this->table_MM, 'tx_sbdownloader_cat.uid = ' .$this->table_MM.'.uid_local AND  '.$this->table_MM.'.uid_foreign='.$row['l18n_parent'].$SPaddWhere,'', 'mmsorting');
				}
				// print_r($unitres);
				
				$units = array();
				$NACats = array();
				$na = false;
				// while ($unitRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($unitres)) {
//					if(in_array($unitRow['uid'],$notAllowedItems)) {
//						$units[$unitRow['uid']] = $NACats[] = '<p style="padding:0px;color:red;font-weight:bold;">- '.$unitRow['name'].' <span class="typo3-dimmed"><em>['.$unitRow['uid'].']</em></span></p>';
//						$na = true;
//					} else {
						// $units[$unitRow['uid']] = '<p style="padding:0px;">- '.$unitRow['name'].' <span class="typo3-dimmed"><em>['.$unitRow['uid'].']</em></span></p>';
//					}
				// }
				
				// typo3 6.2 fix
				// while ($unitRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($unitres)) {
					// $units[$unitRow['uid']] = '<p style="padding:0px;">- '.$unitRow['name'].' <span class="typo3-dimmed"><em>['.$unitRow['uid'].']</em></span></p>';
				// }
				if($na) {
					$this->NA_Items = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">SAVING DISABLED!! <br />'.($row['l18n_parent']&&$row['sys_language_uid']?'The translation original of this':'This').' record has the following units assigned that are not defined in your BE usergroup: '.implode($NACats,chr(10)).'</td></tr></tbody></table>';
				}
				$item = implode($units,chr(10));

				if ($item) {
					$item = 'Categories from the translation original of this record:<br />'.$item;
				} else {
					$item = 'The translation original of this record has no units assigned.<br />';
				}
				$item = '<div class="typo3-TCEforms-originalLanguageValue">'.$item.'</div>';
			} else { // build tree selector
				$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';

					// Set max and min items:
				// $maxitems = t3lib_utility_Math::forceIntegerInRange($config['maxitems'], 0);  
	
				// if(t3lib_div::compat_version('4.5')) {
					// $maxitems = t3lib_div::intInRange($config['maxitems'], 0);  					
				// }else{
					$maxitems = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($config['maxitems'], 0);  					
				// }
				if (!$maxitems)	$maxitems=100000;
				// $minitems = t3lib_utility_Math::forceIntegerInRange($config['minitems'], 0);  
				// if(t3lib_div::compat_version('4.5')) {
					// $minitems = t3lib_div::intInRange($config['minitems'], 0);  					
				// }else{
					$minitems = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($config['minitems'], 0);  					
				// }				

					// Register the required number of elements:
				$this->pObj->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);


				if($config['treeView'] AND $config['foreign_table']) {
				
					global $TCA, $LANG;

					if ($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sb_downloader']) { // get sb_downloader extConf array
						$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['sb_downloader']);
					}
					// if ($confArr['useStoragePid']) {

					// check if record storage pid exists
					$TSconfig = \TYPO3\CMS\Backend\Utility\BackendUtility::getTCEFORM_TSconfig($table,$row);					
					if(!empty($TSconfig['_THIS_ROW']['pages'])){
						$sPid = $TSconfig['_THIS_ROW']['pages'];
						$sPids=explode(",",$sPid);
						foreach($sPids as $value) {
							$sValue = explode("|",$value);
							$sVal = explode("_",$sValue[0]);
							$storagePid[] = $sVal[1];
						}						
						$storagePid = implode(',',$storagePid);
					}else{						
						$storagePid = $TSconfig['_STORAGE_PID']?$TSconfig['_STORAGE_PID']:0; 
					}

					if(!empty($storagePid)){
						$SPaddWhere = ' AND tx_sbdownloader_cat.pid IN (' . $storagePid . ')'; 
					}
					
					
//					if ($GLOBALS['BE_USER']->getTSConfigVal('options.useListOfAllowedItems') && !$GLOBALS['BE_USER']->isAdmin()) {
//						$notAllowedItems = $this->getNotAllowedItems($PA,$SPaddWhere);
//					}

					if($config['treeViewClass'] AND is_object($treeViewObj = &\TYPO3\CMS\Core\Utility\GeneralUtility::getUserObj($config['treeViewClass'],'user_',false))){
					} else {
						$treeViewObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_sbdownloader_tceFunc_selectTreeView');
					}
					$treeViewObj->table = $config['foreign_table'];
					$treeViewObj->table_MM = $config['table_MM'];
					$treeViewObj->init($SPaddWhere);
					$treeViewObj->backPath = $this->pObj->backPath;
					$treeViewObj->parentField = $config['table_MM'].'.uid_foreign'; //$TCA[$config['foreign_table']]['ctrl']['parent_bu'];
					$treeViewObj->expandAll = 1;
					$treeViewObj->expandFirst = 1;
					$treeViewObj->fieldArray = array( $config['foreign_table'].'.uid', $config['foreign_table'].'.cat'); //,'name' those fields will be filled to the array $treeViewObj->tree

					$treeViewObj->ext_IconMode = '0'; // no context menu on icons
					$treeViewObj->title = $LANG->sL($TCA[$config['foreign_table']]['ctrl']['title']);

					$treeViewObj->TCEforms_itemFormElName = $PA['itemFormElName'];
//t3lib_div::debug($LANG->sL($TCA[$config['foreign_table']]['ctrl']['title']));

					if ($table == $config['foreign_table']) {		//if ($table == 'tx_sbdownloader_cat'){
						if (intval($row['uid'])){
							$treeViewObj->TCEforms_nonSelectableItemsArray[] = $row['uid'];
							$notAllowedItems = $this->getNotSelectableItems($PA,$row['uid'],$SPaddWhere);
						}
					} else {
						foreach (explode(',',$row[$field]) as $itemTemp){
							$itemArrTemp=array();
							$itemArrTemp=explode('|',$itemTemp);
							$notAllowedItems[] = $itemArrTemp[0];
						}
					}
					if (is_array($notAllowedItems) && $notAllowedItems[0]) {
						foreach ($notAllowedItems as $k) {
							$treeViewObj->TCEforms_nonSelectableItemsArray[] = $k;
						}
					}
					
						// get default items
					$defItems = array();
					if (is_array($config['items']) && $table == 'tt_content' && $row['CType']=='list' && $row['list_type']==9 && $field == 'pi_flexform')	{
						reset ($config['items']);
						while (list($itemName,$itemValue) = each($config['items']))	{
							if ($itemValue[0]) {
								$ITitle = $this->pObj->sL($itemValue[0]);
								$defItems[] = '<a href="#" onclick="setFormValueFromBrowseWin(\'data['.$table.']['.$row['uid'].']['.$field.'][data][sDEF][lDEF][dynField][vDEF]\','.$itemValue[1].',\''.$ITitle.'\'); return false;" style="text-decoration:none;">'.$ITitle.'</a>';
							}
						}
					}
						// render tree html
					$treeContent=$treeViewObj->getBrowsableTree();
					$treeItemC = count($treeViewObj->ids);

					if ($defItems[0]) { // add default items to the tree table. In this case the value [no units assigned]
						$treeItemC += count($defItems);
						$treeContent .= '<table border="0" cellpadding="0" cellspacing="0"><tr>
							<td>'.$this->pObj->sL($config['itemsHeader']).'&nbsp;</td><td>'.implode($defItems,'<br />').'</td>
							</tr></table>';
					}

						// find recursive units or "storagePid" related errors and if there are some, add a message to the $errorMsg array.
					$errorMsg = $this->findRecursiveUnits($PA,$row,$table,$storagePid,$treeViewObj->ids) ;
					// width of backend field
					$width = 650; // default width for the field with the unit tree
					if (intval($confArr['unitTreeWidth'])) { // if a value is set in extConf take this one.
						$width = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($confArr['unitTreeWidth'],1,600);
					} elseif ($GLOBALS['CLIENT']['BROWSER']=='msie') { // to suppress the unneeded horizontal scrollbar IE needs a width of at least 320px
						$width = 720;
					}

					$config['autoSizeMax'] = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($config['autoSizeMax'],0);
					$height = $config['autoSizeMax'] ? \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($treeItemC+2,\TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($size,1),$config['autoSizeMax']) : $size;
						// hardcoded: 16 is the height of the icons
					$height=$height*16;

					$divStyle = 'position:relative; left:0px; top:0px; height:'.$height.'px; width:'.$width.'px;border:solid 1px;overflow:auto;background:#fff;margin-bottom:5px;';
					$thumbnails='<div  name="'.$PA['itemFormElName'].'_selTree" style="'.htmlspecialchars($divStyle).'">';
					$thumbnails.=$treeContent;
					$thumbnails.='</div>';

				} else {

					$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); '.implode('',$PA['fieldChangeFunc']);

						// Put together the select form with selected elements:
					$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"';
					$size = $config['autoSizeMax'] ? \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange(count($itemArray)+1,\TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($size,1),$config['autoSizeMax']) : $size;
					$thumbnails = '<select style="width:150px;" name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
					#$thumbnails = '<select                       name="'.$PA['itemFormElName'].'_sel"'.$this->pObj->insertDefStyle('select').($size?' size="'.$size.'"':'').' onchange="'.htmlspecialchars($sOnChange).'"'.$PA['onFocus'].$selector_itemListStyle.'>';
					foreach($selItems as $p)	{
						$thumbnails.= '<option value="'.htmlspecialchars($p[1]).'">'.htmlspecialchars($p[0]).'</option>';
					}
					$thumbnails.= '</select>';

				}

				// Perform modification of the selected items array:
				$itemArray = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',',$PA['itemFormElValue'],1);
				// print_r($PA['itemFormElValue']);
				// print_r($itemArray);
				// exit;
				
				foreach($itemArray as $tk => $tv) {
					$tvP = explode('|',$tv,2);
					if (in_array($tvP[0],$removeItems) && !$PA['fieldTSConfig']['disableNoMatchingValueElement'])	{
						$tvP[1] = rawurlencode($nMV_label);
					} elseif (isset($PA['fieldTSConfig']['altLabels.'][$tvP[0]])) {
						$tvP[1] = rawurlencode($this->pObj->sL($PA['fieldTSConfig']['altLabels.'][$tvP[0]]));
					} else {
						$tvP[1] = rawurlencode($this->pObj->sL(rawurldecode($tvP[1])));
					}
					$itemArray[$tk]=implode('|',$tvP);
				}
				$sWidth = 200; // default width for the left field of the unit select
				if (intval($confArr['unitSelectedWidth'])) {
					$sWidth = \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($confArr['unitSelectedWidth'],1,600);
				}
				$params=array(
					'size' => $size,
					'autoSizeMax' => \TYPO3\CMS\Core\Utility\MathUtility::forceIntegerInRange($config['autoSizeMax'],0),
					#'style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$this->pObj->defaultMultipleSelectorStyle.'"',
					'style' => ' style="width:'.$sWidth.'px;"',
					'dontShowMoveIcons' => ($maxitems<=1),
					'maxitems' => $maxitems,
					'info' => '',
					'headers' => array(
						'selector' => $this->pObj->getLL('l_selected').':<br />',
						'items' => $this->pObj->getLL('l_items').':<br />'
					),
					'noBrowser' => 1,
					'thumbnails' => $thumbnails
				);
				$item.= $this->pObj->dbFileIcons($PA['itemFormElName'],'','',$itemArray,'',$params,$PA['onFocus']);
				// Wizards:
				$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
				$item = $this->pObj->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);
			}
		}

		return $this->NA_Items.implode($errorMsg,chr(10)).$item;

	}

	/** parent could not be a child
	 * This function checks if there are units selectable that are not allowed for this BE user and if the current record has
	 * already units assigned that are not allowed.
	 * If such units were found they will be returned and "$this->NA_Items" is filled with an error message.
	 * The array "$itemArr" which will be returned contains the list of all non-selectable units. This array will be added to "$treeViewObj->TCEforms_nonSelectableItemsArray". If a unit is in this array the "select item" link will not be added to it.
	 *
	 * @param	array		$PA: the paramter array
	 * @param	string		$SPaddWhere: this string is added to the query for units when "useStoragePid" is set.
	 * @return	array		array with not allowed units
	 * @see tx_riorganisation_tceFunc_selectTreeView::wrapTitle()
	 */
	function getNotSelectableItems($PA,$parentId,$SPaddWhere) {
		$fTable = $PA['fieldConf']['config']['foreign_table'];
		$fMM = $PA['fieldConf']['config']['table_MM'];
			// get list of allowed units for the current BE user
		//$allowedItemsList=$GLOBALS['BE_USER']->getTSConfigVal('ri_organisationPerms.'.$fTable.'.allowedItems');

		$itemArr = array();
		//if ($allowedItemsList) {
				// get all chield units
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid',$fTable.','.$fMM, 
						'tx_sbdownloader_cat.uid = ' .$fMM.'.uid_local AND ' .$fMM.'.uid_foreign = '.$parentId.
						t3lib_BEfunc::deleteClause($this->table).
						$this->clause,	// whereClauseMightContainGroupOrderBy
						'',
						$this->orderByFields
					);

			//$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $fTable, '1=1' .$SPaddWhere. ' AND NOT deleted');
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
//				if(!t3lib_div::inList($allowedItemsList,$row['uid'])) { // remove all allowed units from the category result
					$itemArr[]=$row['uid'];
					$itemArrTemp=array();
					$itemArrTemp=$this->getNotSelectableItems($PA,$row['uid'],$SPaddWhere);
					foreach ($itemArrTemp as $item){
						$itemArr[]=$item;
					}
//				}
			}
			if (!$PA['row']['sys_language_uid'] && !$PA['row']['l18n_parent']) {
				$catvals = explode(',',$PA['row']['category']); // get units from the current record
				$notAllowedCats = array();
				foreach ($catvals as $k) {
					$c = explode('|',$k);
					if($c[0] && !\TYPO3\CMS\Core\Utility\GeneralUtility::inList($allowedItemsList,$c[0])) {
						$notAllowedCats[]= '<p style="padding:0px;color:red;font-weight:bold;">- '.$c[1].' <span class="typo3-dimmed"><em>['.$c[0].']</em></span></p>';
					}
				}
				if ($notAllowedCats[0]) {
					$this->NA_Items = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">SAVING DISABLED!! <br />This record has the following units assigned that are not defined in your BE usergroup: '.implode($notAllowedCats,chr(10)).'</td></tr></tbody></table>';
				}
//			}
		}
		return $itemArr;
	}


	/**
	 * detects recursive units and returns an error message if recursive units where found
	 *
	 * @param	array		$PA: the paramter array
	 * @param	array		$row: the current row
	 * @param	array		$table: current table
	 * @param	integer		$storagePid: the StoragePid (pid of the category folder)
	 * @param	array		$treeIds: array with the ids of the units in the tree
	 * @return	array		error messages
	 */
	function findRecursiveUnits ($PA,$row,$table,$storagePid,$treeIds) {
		$errorMsg = array();
		if ($table == 'tt_content' && $row['CType']=='list' && $row['list_type']==9) { // = tt_content element which inserts plugin sb_downloader
			$cfgArr = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($row['pi_flexform']);
			if (is_array($cfgArr) && is_array($cfgArr['data']['sDEF']['lDEF']) && $cfgArr['data']['sDEF']['lDEF']['dynField']) {
				$rcList = $this->compareCategoryVals ($treeIds,$cfgArr['data']['sDEF']['lDEF']['dynField']['vDEF']);
			}
		} elseif ($table == $this->table_MM || $table == 'tx_sbdownloader_cat') {
			if ($table == 'tx_sbdownloader_cat' && $row['pid'] == $storagePid && intval($row['uid']) && !in_array($row['uid'],$treeIds))	{ // if the selected category is not empty and not in the array of tree-uids it seems to be part of a chain of recursive units
				$recursionMsg = 'RECURSIVE CATEGORIES DETECTED!! <br />This record is part of a chain of recursive units. The affected units will not be displayed in the category tree.  You should remove the parent category of this record to prevent this.'.$storagePid.';'.implode($treeIds,',');
			}
			if ($table == 'tx_sbdownloader_cat' && $row[$PA['field']]) { // find recursive units in the ri_organisation_businessunit db-record
				$rcList = $this->compareCategoryVals ($treeIds,$row[$PA['field']]);
			}
			// in case of localized records this doesn't work
			if ($storagePid && $row['pid'] != $storagePid && $table == 'tx_sbdownloader_cat') { // if a storagePid is defined but the current category is not stored in storagePid
				$errorMsg[] = '<p style="padding:10px;"><img src="gfx/icon_warning.gif" class="absmiddle" alt="" height="16" width="18"><strong style="color:red;"> Warning:</strong><br />sb_downloader is configured to display units only from the "General record storage page" (GRSP). The current category is not located in the GRSP and will so not be displayed. To solve this you should either define a GRSP or disable "Use StoragePid" in the extension manager.</p>';
			}
		}
		if (strlen($rcList)) {
			$recursionMsg = 'RECURSIVE CATEGORIES DETECTED!! <br />This record has the following recursive units assigned: '.$rcList.'<br />Recursive units will not be shown in the category tree and will therefore not be selectable. ';

			if ($table == 'tx_sbdownloader_cat') {
				$recursionMsg .= 'To solve this problem mark these units in the left select field, click on "edit category" and clear the field "parent category" of the recursive category.';
			} else {
				$recursionMsg .= 'To solve this problem you should clear the field "parent category" of the recursive category.';
			}
		}
		if ($recursionMsg) $errorMsg[] = '<table class="warningbox" border="0" cellpadding="0" cellspacing="0"><tbody><tr><td><img src="gfx/icon_fatalerror.gif" class="absmiddle" alt="" height="16" width="18">'.$recursionMsg.'</td></tr></tbody></table>';
		return $errorMsg;
	}

	/**
	 * This function compares the selected units ($unitString) with the units from the category tree ($treeIds).
	 * If there are units selected that are not present in the array $treeIds it assumes that those units are
	 * parts of a chain of recursive units returns their uids.
	 *
	 * @param	array		$treeIds: array with the ids of the units in the tree
	 * @param	string		$unitString: the selected units in a string (format: uid|title,uid|title,...)
	 * @return	string		list of recursive units
	 */
	function compareCategoryVals ($treeIds,$unitString) {
		$recursiveCategories = array();
		$showncats = implode($treeIds,','); // the displayed units (tree)
		$catvals = explode(',',$unitString); // units of the current record (left field)
		foreach ($catvals as $k) {
			$c = explode('|',$k);
			if(!\TYPO3\CMS\Core\Utility\GeneralUtility::inList($showncats,$c[0])) {
				$recursiveCategories[]=$c;
			}
		}
		if ($recursiveCategories[0])  {
			$rcArr = array();
			foreach ($recursiveCategories as $key => $unit) {
				if ($unit[0]) $rcArr[] = $unit[1].' ('.$unit[0].')'; // format result: title (uid)
			}
			$rcList = implode($rcArr,', ');
		}
		return $rcList;
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/class.tx_sbdownloader_treeview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sb_downloader/class.tx_sbdownloader_treeview.php']);
}
?>
