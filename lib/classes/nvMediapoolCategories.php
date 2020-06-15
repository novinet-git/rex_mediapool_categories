<?php

class nvMediapoolCategories {

	public function __construct() {

	}


	public function getTree($iParentId=0,$iLevel=0) {
		$aItems = array();
		$oItems = rex_sql::factory();
		$sQuery = "SELECT * FROM ".rex::getTablePrefix() . "media_category WHERE parent_id = '$iParentId' ORDER BY name ASC";
		$oItems->setQuery($sQuery);

		foreach($oItems AS $oItem) {
			array_push($aItems,array('name' => $oItem->getValue('name'),'level' => $iLevel, 'id' => $oItem->getValue('id'), 'parent_id' => $oItem->getValue('parent_id'), 'children' => $this->getTree($oItem->getValue('id'),$iLevel+1)));
		}

		return $aItems;
	}

	public function parseTreeList($aItems) {
		//print_r($aItems);
		$aOut = array();
		//$aOut[] = "<ul>";

		$aTree = $this->getTree();

		foreach($aItems AS $aItem) {
			$aOut[] = '<div class="row">';
			$sMarginLeft = $aItem['level']*30;
			$sMaxWidth = 600-$sMarginLeft;

			$aOut[] = '<div class="col-sm-4 mr-3" style="margin-left:'.$sMarginLeft.'px;max-width:'.$sMaxWidth.'px"><input class="form-control" name="name['.$aItem['id'].']" type="text" value="'.$aItem['name'].'"></div>';
			$aOut[] = '<div class="col-sm-3"><select data-yform-tools-select2="" class="form-control select2-hidden-accessible" name="parent['.$aItem['id'].']"><option value="0">Kein Elternelement</option>'.$this->parseTreeSelection($aTree,$aItem).'</select></div>';
			$aOut[] = '</div><br>';
			if (count($aItem['children'])) {
				$aOut[] = $this->parseTreeList($aItem['children']);
			}
		}
		//$aOut[] = "</ul>";
		$sOut = implode("\n",$aOut);
		return $sOut;
	}

	public function parseTreeSelection($aItems,$aCheckItem) {
		//print_r($aItems);
		$aOut = array();

		foreach($aItems AS $aItem) {
			if ($aItem['id'] != $aCheckItem['id'] && $aItem['parent_id'] != $aCheckItem['id']) {
				$aOut[] = '<option value="'.$aItem['id'].'" ';
				if ($aCheckItem['parent_id'] == $aItem['id']) $aOut[] = 'selected';
				$aOut[] = '>';
				for($x=0;$x<$aItem['level'];$x++) {
					$aOut[] = '&nbsp;&nbsp;';
				}
			
				$aOut[] = $aItem['name'].'</option>';
			}
			if (count($aItem['children'])) {
				$aOut[] = $this->parseTreeSelection($aItem['children'],$aCheckItem);
			}
		}
		$sOut = implode("\n",$aOut);
		return $sOut;
	}

	public function updateCategory($iCategoryId,$aData) {
		$oDb = rex_sql::factory();
		$oDb->setTable(rex::getTablePrefix() . 'media_category');
		$oDb->setWhere(['id' => $iCategoryId]);
		$oDb->setValue('name', $aData['name']);

		if ($aData['parent_id']) {
			$oParent = rex_media_category::get($aData['parent_id']);
			if ($oParent->getId()) {
				$sPath = '|';
				$sPath = $oParent->getPath() . $oParent->getId().'|';
				$oDb->setValue('parent_id', $oParent->getId());
				$oDb->setValue('path', $sPath);
			}
		} else {
			$oDb->setValue('parent_id', 0);
			$oDb->setValue('path', "|");
		}

		$oDb->addGlobalUpdateFields();
		$oDb->update();

		rex_media_cache::deleteCategory($iCategoryId);

	}

}
