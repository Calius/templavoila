<?php
/***************************************************************
*  Copyright notice
*  
*  (c) 2003 Kasper Sk�rh�j (kasper@typo3.com)
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
 * Class/Function which manipulates the item-array for table/field tx_templavoila_tmplobj_datastructure.
 *
 * @author    Kasper Sk�rh�j <kasper@typo3.com>
 */






/**
 * Class/Function which manipulates the item-array for table/field tx_templavoila_tmplobj_datastructure.
 * 
 * @author    Kasper Sk�rh�j <kasper@typo3.com>
 */
class tx_templavoila_handleStaticDataStructures {
	var $prefix = 'Static: ';

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds ALL available structures
	 * 
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void		
	 */
    function main(&$params,&$pObj)    {
		// Adding an item!
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				$params['items'][]=Array($this->prefix.$val['title'], $val['path'], $val['icon']);
			}
		}
    }

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Page Templates
	 * 
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void		
	 */
	function main_scope1(&$params,&$pObj)    {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				if ($val['scope']==1)	{
					$params['items'][]=Array($this->prefix.$val['title'], $val['path'], $val['icon']);
				}
			}
		}
	}

	/**
	 * Adds static data structures to selector box items arrays.
	 * Adds only structures for Flexible Content elements
	 * 
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void		
	 */
	function main_scope2(&$params,&$pObj)    {
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures']))	{
			foreach($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'] as $val)	{
				if ($val['scope']==2)	{
					$params['items'][]=Array($this->prefix.$val['title'], $val['path'], $val['icon']);
				}
			}
		}
	}
	
	/**
	 * Adds Template Object records to selector box for Content Elements of the "Plugin" type.
	 * 
	 * @param	array		Array of items passed by reference.
	 * @param	object		The parent object (t3lib_TCEforms / t3lib_transferData depending on context)
	 * @return	void		
	 */
	function pi_templates(&$params,$pObj)	{
			// Find the template data structure that belongs to this plugin:
		$piKey = $params['row']['list_type'];
		$templateRef = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['piKey2DSMap'][$piKey];	// This should be a value of a Data Structure.
		$storagePid = intval($pObj->cachedTSconfig[$params['table'].':'.$params['row']['uid']]['_STORAGE_PID']);		// This should be the Storage PID (at least if the pObj is TCEforms! and t3lib_transferdata is not triggering this function since it is not a real foreign-table thing...)
		
		if ($templateRef && $storagePid)	{
				// Load the table:
			t3lib_div::loadTCA('tx_templavoila_tmplobj');

				// Select all Template Object Records from storage folder, which are parent records and which has the data structure for the plugin:
			$query = 'SELECT title,uid,previewicon FROM tx_templavoila_tmplobj WHERE tx_templavoila_tmplobj.pid='.$storagePid.' AND tx_templavoila_tmplobj.datastructure="'.addslashes($templateRef).'" AND tx_templavoila_tmplobj.parent=0 ORDER BY tx_templavoila_tmplobj.title';
			$res = mysql(TYPO3_db,$query);
			
				// Traverse these and add them. Icons are set too if applicable.
			while($row=mysql_fetch_assoc($res))	{
				if ($row['previewicon'])	{
					$icon='../'.$GLOBALS['TCA']['tx_templavoila_tmplobj']['columns']['previewicon']['config']['uploadfolder'].'/'.$row['previewicon'];
				} else $icon='';
				$params['items'][]=Array($row['title'],$row['uid'],$icon);
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_handlestaticdatastructures.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/class.tx_templavoila_handlestaticdatastructures.php']);
}
?>
