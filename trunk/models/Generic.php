<?php


class Model_Generic extends Zend_Db_Table_Abstract {
	public $db;
       
    function __construct() {
		$this->db = Zend_Db::factory(Zend_Registry::get('iniconfig')->imrs->db->adapter,
			$config=array('host' => Zend_Registry::get('iniconfig')->imrs->db->params->host,
			'username' => Zend_Registry::get('iniconfig')->imrs->db->params->username,
			'password' => Zend_Registry::get('iniconfig')->imrs->db->params->password,
			'dbname' => Zend_Registry::get('iniconfig')->imrs->db->params->dbname)
			);
    } 
        
		

	function insertData($table,$data) {

            $this->db->insert($table, $data);		
            return $this->db->lastInsertId();
	}	
	
	function getData($table,$condition=null,$order=null,$limit=null,$join=null) {
	     	
			
            $sql = "select * from $table  ";

			if($join){
                    $sql .=" join $join  ";
            }
			
            if($condition){
                    $sql .=" where $condition  ";
            }
			
            if($order){
                    $sql .=" order by ".$order;
            }			

            if($limit > 0) {
             $sql .= " limit $limit";
            }
			
            $results = $this->db->fetchAll($sql);

            return $results;
	}
	
	function deleteData($table,$condition) {
		$this->db->delete($table, $condition);	

	}	

	function updateData($table,$condition,$key) {
		$this->db->update($table, $condition,$key);	
	}
	
        //old school query style for old dogs
	function oldSkul($query,$return=true) {
            $stmt = $this->db->query($query);

            if($return == true){
                $rows = $stmt->fetchAll();			
                return $rows;
            }
	}
        
	//old school query style for old dogs
	function deloldSkul($query) {
            $delete = $this->db->query($query);
	}	
	

	function genericForm ($table) {
            $sql  = "show columns from $table";
            $results = $this->db->fetchAll($sql);

            return $results;
	}

	function tableProperties($table) {
		$generic_table = $this->genericForm($table);
		
		$formatted_array = array();
		if(isset($generic_table)) {
			foreach ($generic_table as $fields) {
				$formatted_array[$fields['Field']] = $fields;
			}
		}
		return $formatted_array;
	}
	
	
    function getCustomOrder($table) {
            $results = $this->getData($table,null,"custom_order");	
            return $results;			
	}
		
}
