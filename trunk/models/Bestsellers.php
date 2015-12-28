<?php 

class Model_Bestsellers extends Model_Generic {// note the class name

	public function setDefaultSetting() {
		$raw = $this->oldSkul("select count(ProductID) c, ProductID from Transactions group by ProductID order by c desc limit 8 ");
		if($raw) {
			$this->deleteData('best_sellers', '');
			$i = 1;
			foreach ($raw as $row) {
				$this->insertData('best_sellers', array('ProductID' => $row['ProductID'], 'order' => $i));
				$i++;
			}
		}
	}

	public function selectNextOrder() {
		$sqlQuery = "select max(`order`) + 1 as m from best_sellers";
		$resQuery = $this->oldSkul($sqlQuery);
		if (empty($resQuery[0]['m'])) {
			return '1';
		} else {
			return $resQuery[0]['m'];
		}
	}

}

