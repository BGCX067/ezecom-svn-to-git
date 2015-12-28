<?php

//Collection of static and basic functions

class Model_Categories extends Model_Generic {

	public function updateCategory($postFields) {

		$CategoryID = isset($postFields['CategoryID']) ? $postFields['CategoryID'] : '0';
		$Name = isset($postFields['CategoryName']) ? $postFields['CategoryName'] : '';
		$LastEdited = date('Y-m-d H:i:s');
		$isPrimary = isset($postFields['isPrimary']) ? $postFields['isPrimary'] : '0';
		$FamilyCategories = isset($postFields['FamilyCategories']) ? $postFields['FamilyCategories'] : array();

		$strUpdate = "update Category set isPrimary = $isPrimary, LastEdited = '$LastEdited', Name = ". $this->db->quote($Name) ." where ID = $CategoryID";
		$sqlUpdate = $this->db->query($strUpdate);
		
		$strCleanUpChildren = "delete from ParentChildCategories where ChildID = $CategoryID";
		$sqlCleanUpChildren = $this->db->query($strCleanUpChildren);

		$strCleanUpParents = "delete from ParentChildCategories where ParentID = $CategoryID";
		$sqlCleanUpParents = $this->db->query($strCleanUpParents);

		foreach ($FamilyCategories as $FamilyCategory) {
			if ($isPrimary == '1') {
				$strInsert = "insert into ParentChildCategories(ParentID, ChildID) values($CategoryID, $FamilyCategory)";
			} else {
				$strInsert = "insert into ParentChildCategories(ParentID, ChildID) values($FamilyCategory, $CategoryID)";
			}
			$sqlInsert = $this->db->query($strInsert);
		}

		if ($sqlUpdate && $sqlCleanUpChildren && $sqlCleanUpParents) {
			return true;
		} else {
			return false;
		}
	}

	public function createCategory($postFields) {

		$Created = date('Y-m-d H:i:s');

		if ($postFields['ParentCategoryID'] == '0') {
			$strCheckCategory = "select * from Category where Name = " . $this->db->quote($postFields['ParentCategoryName']);
			$resCheckCategory = $this->db->fetchAll($strCheckCategory);
			if (empty($resCheckCategory)) {
				$strInsertCategory = "insert into Category(Created, Name) 
				values('$Created', ". $this->db->quote($postFields['ParentCategoryName']) .")";
				$sqlInsertCategory = $this->db->query($strInsertCategory);
				if (!$sqlInsertCategory) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
				$postFields['ParentCategoryID'] = $this->db->lastInsertId();
			} else {
				return array("false", $postFields['ParentCategoryName'] . " already exist.");
			}
		}

		if ($postFields['SubParentCategoryID'] == '0') {
			$strCheckCategory = "select * from Category where Name = " . $this->db->quote($postFields['SubParentCategoryName']);
			$resCheckCategory = $this->db->fetchAll($strCheckCategory);
			if (empty($resCheckCategory)) {
				$strInsertCategory = "insert into Category(Created, Name) 
				values('$Created', ". $this->db->quote($postFields['SubParentCategoryName']) .")";
				$sqlInsertCategory = $this->db->query($strInsertCategory);
				if (!$sqlInsertCategory) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
				$postFields['SubParentCategoryID'] = $this->db->lastInsertId();
			} else {
				return array("false", $postFields['SubParentCategoryName'] . " already exist.");
			}
		}

		if ($postFields['ChildCategoryID'] == '0') {
			$strCheckCategory = "select * from Category where Name = " . $this->db->quote($postFields['ChildCategoryName']);
			$resCheckCategory = $this->db->fetchAll($strCheckCategory);
			if (empty($resCheckCategory)) {
				$strInsertCategory = "insert into Category(Created, Name) 
				values('$Created', ". $this->db->quote($postFields['ChildCategoryName']) .")";
				$sqlInsertCategory = $this->db->query($strInsertCategory);
				if (!$sqlInsertCategory) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
				$postFields['ChildCategoryID'] = $this->db->lastInsertId();
			} else {
				return array("false", $postFields['ChildCategoryName'] . " already exist.");
			}
		}

		$postFields['SubParentCategoryID'] = $postFields['SubParentCategoryID'] == '-1' ? '0' : $postFields['SubParentCategoryID'];
		$postFields['ChildCategoryID'] = $postFields['ChildCategoryID'] == '-1' ? '0' : $postFields['ChildCategoryID'];

		$aSqlWhere = array();
		if ($postFields['ParentCategoryID'] != '0') {
			$aSqlWhere[] = " ParentID = {$postFields['ParentCategoryID']} ";
		}
		if ($postFields['SubParentCategoryID'] != '0') {
			$aSqlWhere[] = " SubParentID = {$postFields['SubParentCategoryID']} ";
		}
		if ($postFields['ChildCategoryID'] != '0') {
			$aSqlWhere[] = " ChildID = {$postFields['ChildCategoryID']} ";
		}
		$strWhere = empty($aSqlWhere) ? '' : ' where ' . implode(' and ', $aSqlWhere);
		$strCheckUniqueTreeID = "select * from ParentChildCategories $strWhere";

		$resCheckUniqueTreeID = $this->db->fetchAll($strCheckUniqueTreeID);
		if (empty($resCheckUniqueTreeID)) {
		
			if ( $postFields['ParentCategoryID'] != '0' && $postFields['SubParentCategoryID'] == '0' && $postFields['ChildCategoryID'] == '0' ) {
				$strInsertTreeBranch = "replace into ParentChildCategories(ParentID, SubParentID, ChildID) values 
				({$postFields['ParentCategoryID']}, 0, 0)";

				$sqlInsertTreeBranch = $this->db->query($strInsertTreeBranch);
				if (!$sqlInsertTreeBranch) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
			} else if ( $postFields['ParentCategoryID'] != '0' && $postFields['SubParentCategoryID'] != '0' && $postFields['ChildCategoryID'] == '0' ) {
				$strInsertTreeBranch = "replace into ParentChildCategories(ParentID, SubParentID, ChildID) values 
				({$postFields['ParentCategoryID']}, 0, 0),
				({$postFields['ParentCategoryID']}, {$postFields['SubParentCategoryID']}, 0)";

				$sqlInsertTreeBranch = $this->db->query($strInsertTreeBranch);
				if (!$sqlInsertTreeBranch) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
			} else {
				$strInsertTreeBranch = "replace into ParentChildCategories(ParentID, SubParentID, ChildID) values 
				({$postFields['ParentCategoryID']}, 0, 0),
				({$postFields['ParentCategoryID']}, {$postFields['SubParentCategoryID']}, 0),
				({$postFields['ParentCategoryID']}, {$postFields['SubParentCategoryID']}, {$postFields['ChildCategoryID']})";

				$sqlInsertTreeBranch = $this->db->query($strInsertTreeBranch);
				if (!$sqlInsertTreeBranch) {
					return array("false", "Something went wrong. Please Reset and try again.");
				}
			}

		} else {
			return array("false", "{$postFields['ParentCategoryName']} => {$postFields['SubParentCategoryName']} => {$postFields['ChildCategoryName']} already exist!");
		}
		return array("true", "{$postFields['ParentCategoryName']} => {$postFields['SubParentCategoryName']} => {$postFields['ChildCategoryName']} created!");
	}

	public function create($data) {
		#CustomOrder
		if(isset($data['CustomOrder']) && !empty($data['CustomOrder']) && is_numeric($data['CustomOrder'])) {
			$data['CustomOrder'] = ceil($data['CustomOrder']);
		} else {
			$data['CustomOrder'] = '0';
		}

		#ParentID
		if(isset($data['ParentID']) && !empty($data['ParentID']) && !isset($data['isPrimary'])) {
			$data['ParentID'] = $data['ParentID'];
		} else {
			$data['ParentID'] = '0';
		}

		#isPrimary
		if (isset($data['isPrimary'])) {
			$data['isPrimary'] = '1';
		} else {
			$data['isPrimary'] = '0';
		}
		return $this->insertData('Category',$data);
	}
	
	public function listCategories($category_id=null) {
		$condition = ($category_id) ? 'ID = '.$category_id.' ' : '' ; 
		return $this->getData("Category",$condition);
	}

	public function listCategoryTree() {

		$strParent = "select distinct concat(b.ParentID,'-0-0') as ID, a.Created, a.LastEdited, a.Name, a.ParentID, a.isPrimary, a.CustomOrder 
		from Category a inner join ParentChildCategories b on a.ID = b.ParentID where b.ParentID != 0 order by a.CustomOrder asc, a.Name asc";
		#$strParent = "select concat(ID, '-0-0') as ID, Created, LastEdited, Name, ParentID, isPrimary, CustomOrder from Category where isPrimary=1 order by CustomOrder asc, Name asc";
		$parents = $this->db->fetchAll($strParent);

		$categories = array();
		if($parents) {
			foreach ($parents as $row) {
				$subparent = $this->getSubParents($row['ID']);

				#populate children (3rd level)
				foreach ($subparent as $subparent_key => $subparent_value) {
					$subparent_value['thirdLevel'] = $this->getCategoryChildren($row['ID'], $subparent_value['SubParentID']);
					$subparent[$subparent_key] = $subparent_value;
				}

				$categories[] = array('Parent'=>$row,'Children'=> $subparent);
			}
		}
		
		return $categories;
	}
	
	public function getSubParents($ParentID) {
		$str = "select distinct concat(b.ParentID,'-',b.SubParentID,'-0') as ID, a.Created, a.LastEdited, a.Name, a.ParentID, a.isPrimary, a.CustomOrder, b.SubParentID 
		from Category a inner join ParentChildCategories b on a.ID = b.SubParentID where b.ParentID = $ParentID order by b.CustomOrder asc, a.Name asc";
		return $this->db->fetchAll($str);
	}
	
	public function getCategoryChildren($ParentID, $SubParentID = 0) {
		$str = "select distinct concat(b.ParentID,'-',b.SubParentID,'-',b.ChildID) as ID, a.Created, a.LastEdited, a.Name, a.ParentID, a.isPrimary, a.CustomOrder 
		from Category a inner join ParentChildCategories b on a.ID = b.ChildID where b.ParentID = $ParentID and b.SubParentID = $SubParentID 
		order by b.CustomOrder asc, a.Name asc";
		return $this->db->fetchAll($str);
	}
	
	
	public function listParentCategories($category_id=null) {
		$condition = '';
		if($category_id) {
			$condition .= "and ID !=".$category_id;
		}
		return $this->getData("Category","isPrimary = 1 ".$condition, " CustomOrder ASC, Name ASC ");
	}
	
	public function getParentCategory($category_id) {
		$tmp_id = explode("-",$category_id);
		
		return $this->getData("Category","ID =  ".$tmp_id[0]." and isPrimary = 1 " );
	}

	public function isParent($id) {
		$data = $this->getData("Category","ID = $id and isPrimary = 1 ");
		if (!empty($data)) {
			return true;
		} else {
			return false;
		}
	}
	
	public function getFamilyCategories ($id) {
		if ($this->isParent($id)) { #get children
			$str = "select b.* from ParentChildCategories a inner join Category b on a.ChildID = b.ID 
			where a.ParentID = $id and a.ChildID != 0 order by a.CustomOrder asc, b.Name asc";
		} else { #get parents
			$str = "select b.* from ParentChildCategories a inner join Category b on a.ParentID = b.ID 
			where a.ChildID = $id and a.ParentID != 0 order by b.CustomOrder asc, b.Name asc";
		}
		return $this->db->fetchAll($str);
	}
	
	public function getCategoriesByLevel($level = 'parent', $preSelected = array()) {
		$data = array();
		if ($level == 'parent') {
			$data = $this->getData("Category","isPrimary = 1 ", " CustomOrder ASC, Name asc ");
		}
		if ($level == 'child') {
			$data = $this->getData("Category","isPrimary != 1 ", " CustomOrder ASC, Name asc ");
		}
		foreach ($data as $dkey => $dval) {
			$dval['pre_selected'] = in_array($dval['ID'], $preSelected) ? "checked" : "";
			$data[$dkey] = $dval;
		}
		return $data;
	}

	public function listChildCategories($category_id) {
		$parent = $this->getData("Category","ID=".$category_id." ");
		$children = $this->getData("Category","ParentID=".$category_id." ");
		
		return array('Parent'=>$parent,'Children'=>$children);
	}
	
	public function listOrphanCategories() {
		$str = "select concat('0-0-', ID) as ID, Created, LastEdited, Name, ParentID, isPrimary, CustomOrder from Category 
		where ID not in (select distinct ChildID from ParentChildCategories where ParentID != 0 and SubParentID != 0) 
		and ID not in (select distinct SubParentID from ParentChildCategories where ParentID != 0)
		and ID not in (select distinct ParentID from ParentChildCategories)
		order by CustomOrder ASC, Name ASC";
		return $this->db->fetchAll($str);
	}
	
	public function delete($category_id) {
		//@todo delete Files and Thumbs as well
		$this->oldSkul("delete from Category where ID=".$category_id,false);
	}
	
	public function updateCat($category_id,$data) {
		#get old isPrimary
		$_category = $this->getData("Category"," ID = $category_id ");
		$isPrimary_old = isset($_category[0]['ID']) && !empty($_category) ? $_category[0]['ID'] : '0';
		$isPrimary_new = '0';

		$updateData = array();
		$updateData[] = ' Name = '. $this->db->quote($data['Name']) .' ';
		$updateData[] = ' isPrimary=0 ';
		if(isset($data['isPrimary'])) {
			$updateData[] = ' isPrimary=1 ';
			$isPrimary_new = '1';
		}
		
		#CustomOrder
		$updateData[] = ' CustomOrder=0 ';
		if(isset($data['CustomOrder']) && !empty($data['CustomOrder']) && is_numeric($data['CustomOrder'])) {
			$updateData[] = ' CustomOrder='. ceil($data['CustomOrder']) .' ';
		}

		#ParentID
		$updateData[] = ' ParentID=0 ';
		if(isset($data['ParentID']) && !empty($data['ParentID']) && $isPrimary_new == '0') {
			$updateData[] = ' ParentID='. $data['ParentID'] .' ';
		}

		if ($isPrimary_old != $isPrimary_new && $isPrimary_new == '0') { #parent/primary to child
			$this->oldSkul("update Category set ParentID = 0 where ParentID = $category_id", false);
		}

		$this->oldSkul("update Category set ". implode(',', $updateData) ." where ID=".$category_id, false);
	}
	
}
