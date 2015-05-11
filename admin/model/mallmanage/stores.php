<?php
class ModelMallmanageStores extends Model {
	public function getStores($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "sb_store s LEFT JOIN " . DB_PREFIX . "sb_xstore_meta xsm ON (s.store_id = xsm.store_id) WHERE s.store_id <> 1";

		if (!empty($data['filter_name'])) {
			$sql .= " AND s.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_url'])) {
			$sql .= " AND s.url LIKE '" . $this->db->escape($data['filter_url']) . "%'";
		}

		if (isset($data['filter_status']) && !is_null($data['filter_status'])) {
			$sql .= " AND xsm.status = '" . (int)$data['filter_status'] . "'";
		}
		
		$sql .= " GROUP BY s.store_id";

		$sort_data = array(
			's.name',
			's.url',
			'xsm.status',
			's.store_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY s.store_id";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalStores() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "sb_store s LEFT JOIN " . DB_PREFIX . "sb_xstore_meta xsm ON (s.store_id = xsm.store_id) WHERE s.store_id <> 1");

		return $query->row['total'];
	}

	public function newStore($data){
	    $default_required = array(
	        'name'              => $data['name'],
	        'url'               => $data['store_url'],
	        'email'             => $data['email'],
	        'username'          => $data['username'],
	        'password'          => $data['password']
	    );

	    $columns = array();
	    $values = array();

	    foreach($default_required as $key=>$value){
	        $columns[] = $key ;
	        $values[] = "'" . $value ."'";
	    }

	    $sql = "INSERT INTO `" . DB_PREFIX . "sb_store` (" . implode(", ",$columns) . ")  VALUES ( "  . implode(",",$values) . ")";

	    $this->db->query($sql);

	    $store_id = $this->db->getLastId();

	    $this->db->query("UPDATE `" . DB_PREFIX . "sb_store` SET `db_name` = 'sb_".$store_id."' WHERE `store_id` = '".$store_id."'");

	    $this->db->query("INSERT INTO `" . DB_PREFIX . "sb_xstore_meta` SET store_id = '" . (int)$store_id . "', url_mode = '".$this->db->escape($data['url_type']) ."', domain = '" . $this->db->escape($data['main_domain']) . "'");

	    return $store_id;
  	}

  	public function CheckExists($table,$field,$value,$store_id = 0){
	    $sql = "SELECT count(*) as total FROM " . DB_PREFIX . "`".$table."` WHERE LOWER(`".$field."`) = '".strtolower($value)."'";
	    if($store_id) {
	      $sql.= " AND store_id <> '".$store_id."'";
	    }
	    $query = $this->db->query($sql);
	    if((int)$query->row['total'] > 0) {
	        return $query->row['total'];
	    } else {
	        return false;
	    }
  	}

  	public function deleteStore($store_id) {
	    $this->db->query("DELETE FROM `" . DB_PREFIX . "sb_store` WHERE store_id = '".$store_id."'");   
	    $this->db->query("DELETE FROM `" . DB_PREFIX . "sb_xstore_meta` WHERE store_id = '".$store_id."'");  
	}

	public function getStore($store_id) {
      	$sql = "SELECT * FROM `" . DB_PREFIX . "sb_store` s 
                INNER JOIN `" . DB_PREFIX . "sb_xstore_meta` xsm ON (xsm.store_id=s.store_id) 
                WHERE s.store_id = '".$store_id."' LIMIT 1";
          
      $store_data = $this->db->query($sql)->row;
      return $store_data;
  	}

  	public function editStore($data, $store_id){
	    //$this->db->query("UPDATE `" . DB_PREFIX . "sb_store` SET `name` = '".$this->db->escape($data['name'])."' WHERE `store_id` = '".(int)$store_id."'");
		$this->db->query("UPDATE `" . DB_PREFIX . "sb_xstore_meta` SET status = '" . $this->db->escape($data['store_status']) . "' WHERE store_id = '" . (int)$store_id . "'");
	}
}