<?php
/***************************************************************
* Copyright notice
*
* (c) 2010 Tolleiv Nietsch <nietsch@aoemedia.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
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
 * Class to provide unique access to datastructure
 *
 * @author	Tolleiv Nietsch <nietsch@aoemedia.de>
 */
class tx_templavoila_datastructureRepository {

	/**
	 * Retrieve a single datastructure by uid or xml-file path
	 *
	 * @param integer $uid
	 * @return tx_templavoila_datastructure
	 */
	public function getDatastructureByUidOrFilename($uidOrFile) {

		$className = '';
		if(t3lib_div::intval_positive($uidOrFile) > 0) {
			$className = 'tx_templavoila_datastructure_dbbase';
		} else if(($staticKey = $this->validateStaticDS($uidOrFile)) !== FALSE) {
			$uidOrFile = $staticKey;
			$className = 'tx_templavoila_datastructure_staticbase';
		} else {
			throw new InvalidArgumentException(
				'Argument was supposed to be either a uid or a filename',
				1273409810
			);
		}

		$ds = null;
		if(version_compare(TYPO3_version,'4.3.0','<')) {
			$className = t3lib_div::makeInstanceClassName($className);
			$ds = new $className($uidOrFile);
		} else {
			$ds = t3lib_div::makeInstance($className, $uidOrFile);
		}
		return $ds;
	}

	/**
	 * Retrieve a collection (array) of tx_templavoila_datastructure objects
	 *
	 * @param integer $pid
	 * @return array
	 */
	public function getDatastructuresByStoragePid($pid) {

		$dscollection = array();
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			$confArr = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'];
			foreach ($confArr as $key=>$conf) {
				$ds = $this->getDatastructureByUidOrFilename($conf['path']);
				$pids = $ds->getStoragePids();
				if ($pids == '' || t3lib_div::inList($pids, $pid)) {
					$dscollection[] = $ds;
				}
			}
		}

		if(!self::isStaticDsEnabled()) {
			$dsRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid',
				'tx_templavoila_datastructure',
				'pid=' . intval($pid) . t3lib_BEfunc::deleteClause('tx_templavoila_datastructure')
			);
			foreach ($dsRows as $ds) {
				$dscollection[] = $this->getDatastructureByUidOrFilename($ds['uid']);
			}
		}
		usort($dscollection, array($this, 'sortDatastructures'));
		return $dscollection;
	}

	/**
	 * Retrieve a collection (array) of tx_templavoila_datastructure objects
	 *
	 * @param integer $pid
	 * @param integer $scope
	 * @return array
	 */
	public function getDatastructuresByStoragePidAndScope($pid, $scope) {
		$dscollection = array();
		if (is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			$confArr = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'];
			foreach ($confArr as $key=>$conf) {
				if ($conf['scope'] == $scope) {
					$ds = $this->getDatastructureByUidOrFilename($conf['path']);
					$pids = $ds->getStoragePids();
					if ($pids == '' || t3lib_div::inList($pids, $pid)) {
						$dscollection[] = $ds;
					}
				}
			}
		}

		if(!self::isStaticDsEnabled()) {
			$dsRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
				'uid',
				'tx_templavoila_datastructure',
				'scope=' . intval($scope) . ' AND pid=' . intval($pid) . t3lib_BEfunc::deleteClause('tx_templavoila_datastructure')
			);
			foreach ($dsRows as $ds) {
				$dscollection[] = $this->getDatastructureByUidOrFilename($ds['uid']);
			}
		}
		usort($dscollection, array($this, 'sortDatastructures'));
		return $dscollection;
	}

	/**
	 *
	 * @param string $file
	 * @return mixed
	 */
	protected function validateStaticDS($file) {
		if (!is_array($GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'])) {
			return FALSE;
		}
		$confArr = $GLOBALS['TBE_MODULES_EXT']['xMOD_tx_templavoila_cm1']['staticDataStructures'];
		$confKey = FALSE;
		$fileAbsName = t3lib_div::getFileAbsFileName($file);
		foreach ($confArr as $key=>$conf) {
			if (t3lib_div::getFileAbsFileName($conf['path']) == $fileAbsName) {
				$confKey = $key;
				break;
			}
		}
		return $confKey;
	}

	/**
	 *
	 * @return boolean
	 */
	protected function isStaticDsEnabled() {
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['templavoila']);
		return $extConf['staticDS.']['enable'];
	}

	/**
	 * Sorts datastructure alphabetically
	 *
	 * @param	tx_templavoila_datastructure $obj1
	 * @param	tx_templavoila_datastructure $obj2
	 * @return	int	Result of the comparison (see strcmp())
	 * @see	usort()
	 * @see	strcmp()
	 */
	public function sortDatastructures($obj1, $obj2) {
		return strcmp(strtolower($obj1->getLabel()), strtolower($obj2->getLabel()));
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/classes/class.tx_templavoila_datastructureRepository.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/templavoila/classes/class.tx_templavoila_datastructureRepository.php']);
}
?>
