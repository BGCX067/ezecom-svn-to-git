<?php

//Collection of static and basic functions

class Model_Imageuploader extends Model_Generic {


	public function processUpload($files,$prefix='file_', $resize_params=null, $thumbs_params=null) {
	
		$upload_dir = UPLOADS_PATH;	
		$file_count = count($files['file']['name']);
		
		$processed_files = array();
		for ( $n = 0; $n < $file_count; $n++ ) {
		
			$file_size = $files['file']['size'][$n];
			$tmp_filename = $files['file']['tmp_name'][$n];
			$target_path = $upload_dir . basename( $files['file']['name'][$n]);
			
			$processed_files[] = $this->uploadfileAction($prefix, $file_size, $tmp_filename, $target_path, $resize_params, $thumbs_params);
		}
		return $processed_files;
	}

	/***
	*
	* @param filePrefix string
	* @param resize_params array
	* @param thumbs_params array
	***/
	public function uploadfileAction($filePrefix='file_', $fileSize, $tmpFileName, $destinationFile, $resize_params=null, $thumbs_params = null)	{
 
		if ($fileSize <= 2097152) {

			if(move_uploaded_file($tmpFileName, $destinationFile)) {
			
				$nufile = $filePrefix.uniqid().'.jpg';
				rename($destinationFile, UPLOADS_PATH . '/' . $nufile);				

				$image = new Model_Image();
				$image->load(UPLOADS_PATH . '/' . $nufile);
				
				if($resize_params && !empty($resize_params)) {
					if(count($resize_params) == 1) {
						$image->resizeToHeight($resize_params[0]);
					} 
					else {
						$image->resize($resize_params[0], $resize_params[1]);// 640 x 480
					}
					$image->save(UPLOADS_PATH . '/' . $nufile);
				}
				
				//create thumbs dir if not exist
				if (!is_dir(UPLOADS_PATH . '/thumbs')) {
					mkdir(UPLOADS_PATH . '/thumbs');
				}

				//create thumb image in thumbs dir using Model_Image
				if($thumbs_params && !empty($thumbs_params)) {
					if(count($thumbs_params) == 1) {
						$image->resizeToWidth($thumbs_params[0]);
					} 
					else {
						$image->resize($thumbs_params[0], $thumbs_params[1]);// 640 x 480
					}
					$image->save(UPLOADS_PATH . '/thumbs/th_' . $nufile);
				}
				
				//create db record
				$nufile_id = $this->insertData("Files",array("Created"=>date('Y-m-d H:i:s'),"LastEdited"=>date('Y-m-d H:i:s'), "Filename"=>$nufile ));
			}
		}
		return array('FileID'=>$nufile_id, 'Filename'=>$nufile);
	}
}