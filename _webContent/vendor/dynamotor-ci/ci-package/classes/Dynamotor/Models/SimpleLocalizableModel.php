<?php

/**
 * "SimpleLocalizableModel" Model Class for CodeIgniter
 * @author      Leman Kwok
 * @copyright   Copyright (c) 2013, LMSWork.
 * @license     http://codeigniter.com/user_guide/license.html
 * @link        http://lmswork.com
 * @version     Version 1.2
 *
 */

// ------------------------------------------------------------------------

namespace Dynamotor\Models {
	use HC_Model;

	class SimpleLocalizableModel extends HC_Model {


		// Enable this option can use content with text_locales and able to search keyword from localized content
		var $is_localized = FALSE;
		var $locale_table = 'text_locales';



		public function result_row($row, $options = false) {

			$row = parent::result_row($row, $options);

			if (is_array($row) ) {
				if($this->is_localized){

					if(empty($row['loc_title']) && !empty($row['title'])){
						$row['loc_title'] = $row['title'];
					}
					if(empty($row['loc_description'])){
						$row['loc_description'] = !empty($row['description']) ? $row['description'] : NULL;
					}
					if(empty($row['loc_content'])){
						$row['loc_content'] = !empty($row['content']) ?  $row['content'] : NULL;
					}
					if(empty($row['loc_parameters']) ){
						$row['loc_parameters_str'] = !empty($row['parameters_str']) ? $row['parameters_str'] : NULL;
						$row['loc_parameters'] = !empty($row['parameters']) ? $row['parameters'] : NULL;
					}elseif(isset($row['loc_parameters'])){
						$row['loc_parameters_str'] = $row['loc_parameters'];
						$row['loc_parameters'] = $this->decode_parameters($row['loc_parameters']);
					}
				}
			}
			return $row;
		}
		// you may necessary to overwrite this function for extra selecting options
		protected function selecting_options($options = false) {
			$options = parent::selecting_options($options);


			if($this->is_localized){
				$locale_table = $this->locale_table;
				
				// load locale text content (LEFT JOIN)
				if (!empty($options['_with_locale'])) {
					if (isset($options['_keyword_fields'])) {
						$options['_keyword_fields'][] = $locale_table . '.title';
						$options['_keyword_fields'][] = $locale_table . '.content';
						$options['_keyword_fields'][] = $locale_table . '.description';
						//$options['_keyword_fields'][] = $locale_table . '.parameters';
					}


					$prefix = isset($options['_with_locale_prefix']) ? $options['_with_locale_prefix'] : 'loc_';

					$this->get_db()->select($this->table.'.*,' .
						$locale_table . '.title as '.$prefix.'title,' .
						$locale_table . '.content as '.$prefix.'content,' .
						$locale_table . '.description as '.$prefix.'description,' .
						$locale_table . '.parameters as '.$prefix.'parameters,' .
						$locale_table . '.status as '.$prefix.'status,' .
						$locale_table . '.locale as locale'
					);

					$join_case = '';
					if(in_array('is_live', $this->fields)){
						$join_case.= 'AND ' . $this->table . '.is_live = ' . $this->locale_table . '.is_live ';
					}

					$this->get_db()->join($locale_table,
						$this->table . '.id = ' . $this->locale_table . '.ref_id '
						. $join_case
						. 'AND ' . $this->get_db()->dbprefix($this->locale_table) . '.ref_table = \'' . $this->get_db()->escape_str($this->table) . '\' ',
						'LEFT');
					$this->_or(array('locale IS NULL', $this->_array_to_in_case('locale',$options['_with_locale'] )));

				}
			}

			return $options;
		}
	}
}
