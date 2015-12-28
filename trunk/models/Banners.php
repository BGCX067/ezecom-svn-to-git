<?php

//Collection of static and basic functions

class Model_Banners extends Model_Imageuploader {

	public function create($file,$title=null,$link=null) {
	
		$upload = $this->processUpload($file,'banner_',null,array(200));
		
		$file_id = $upload[0]['FileID'];
		
		$this->insertData('Banners',array("Created"=>date('Y-m-d H:i:s'),"LastEdited"=>date('Y-m-d H:i:s'), "FileID"=>$file_id, 'Title'=>$title, 'URLSegment'=>$link ));
		
		return $upload;
		
	}
	
	public function listBanners($banner_id=null) {
		$condition = ($banner_id) ? ' where t1.ID = '.$banner_id.' ' : '' ; 
		//return $this->getData('Banners',$condition,null,null,'Files on Files.ID = Banners.FileID');
		return $this->oldSkul("select t1.*, Filename from Banners t1 join Files t2 on t2.ID = t1.FileID $condition order by CustomOrder");
	}
	
	public function listBannersByTag($tag=null) {
		$condition = ($tag) ? " where t1.Tags = '$tag' " : "" ; 
	
		$tmp = $this->oldSkul("select t1.*, Filename from Banners t1 join Files t2 on t2.ID = t1.FileID $condition order by CustomOrder");
		
		$data = array();
		foreach($tmp as $row) {
			if( $row['Tags'] != ''){
				$data[$row['Tags']][] = $row;
			}
		}
		
		return $data;
	}
	
	
	
	public function delete($banner_id) {
		//@todo delete Files and Thumbs as well
		$this->oldSkul("delete from Banners where ID=".$banner_id,false);
	}
	
	public function update($banner_id,$data) {
		$this->oldSkul("update Banners set Title='".$data['Title']."', 
		URLSegment='".$data['URLSegment']."',  
		CustomOrder=".$data['CustomOrder']." ,
		CategoryID=".$data['CategoryID']." ,
		Tags='".$data['Tags']."'
		where ID=".$banner_id,false);
	}
	
}