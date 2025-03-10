<?php if(!defined('BASEPATH')) exit('No direct script allowed');

class M_main extends CI_Model{

	function get_user($q) {
		return $this->db->get_where('user_box',$q);
	}

	function get_user_box(){
		return $this->db->get("user_box");
	}

	/***
	 * SELECT user_box.id_user_box, rent.id_box, rent.start_reservation_date, rent.end_reservation_date
	 * FROM user_box
	 * INNER JOIN rent ON rent.id_user_box= user_box.id_user_box
	 * WHERE user_box.id_user_box = $id_user 
	***/
	function isRent($id_user){
		// $isRent = false;
		$this->db->select("user_box.id_user_box, rent.id_box, rent.start_reservation_date, rent.end_reservation_date");
		$this->db->join('rent', 'rent.id_user_box = user_box.id_user_box', 'inner');
		$this->db->where("user_box.id_user_box", $id_user);
		return $this->db->get("user_box");
	}

	/*** 
		SELECT box.id_box,  warehouse.name AS "Warehouse_name", box.current_code, box.generated_code
		FROM box
		INNER JOIN rent ON rent.id_box = box.id_box
		INNER JOIN warehouse ON warehouse.id_warehouse = box.id_warehouse
		WHERE rent.id_user_box = 1 AND rent.id_box = 1 AND "2025-02-06 02:55:39" BETWEEN rent.start_reservation_date AND rent.end_reservation_date
	***/
	function get_code_box($id_user,$actual_date,$id_box){
		$this->db->select("box.id_box,  warehouse.name, box.current_code, box.generated_code");
		$this->db->join('rent', 'rent.id_box = box.id_box', 'inner');
		$this->db->join('warehouse', 'warehouse.id_warehouse = box.id_warehouse', 'inner');
		$this->db->where("rent.id_user_box", $id_user);
		$this->db->where("rent.id_box", $id_box);
		$this->db->where("'$actual_date' BETWEEN rent.start_reservation_date AND rent.end_reservation_date", NULL, FALSE);
		return $this->db->get('box');
	}

	/*** 
		UPDATE user_box SET user_box.fcm="fcm" WHERE user_box.id_user_box = id_user_box
 	***/
	function update_user_fcm($fcm,$id_user){
		$this->db->set('fcm', $fcm);
		$this->db->where('id_user_box', $id_user);
		$this->db->update('user_box');
	}

	
	
}