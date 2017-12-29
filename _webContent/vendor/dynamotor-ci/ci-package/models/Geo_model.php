<?php if (!defined('BASEPATH')) die('No direct script access allowed');

class Geo_model extends \Dynamotor\Core\HC_Model {

	function get_regions() {
		$query = $this->db->order_by('priority asc')->where('status', 1)->get('geo_regions');
		return $query->result_array();
	}

	function get_districts($field_based = false) {
		$query = $this->db->order_by('priority asc')->where('status', 1)->get('geo_districts');
		$rows  = $query->result_array();
		if (is_string($field_based) && strlen($field_based) > 0) {
			$output = array();
			foreach ($rows as $idx => $row) {
				if (isset($row[$field_based])) {
					$ref_id = $row[$field_based];
					if (!isset($output[$ref_id])) {
						$output[$ref_id] = array();
					}

					$output[$ref_id][] = $row;
				}
			}
			return $output;
		}
		return $rows;
	}
}
