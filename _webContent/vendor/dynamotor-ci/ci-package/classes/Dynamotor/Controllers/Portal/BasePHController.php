<?php 
namespace Dynamotor\Controllers\Portal;

// MY_Controller should be defined in apps/core/MY_Controller.php
use \MY_Controller;
use \Dynamotor\Helpers\PostHelper;
use \Dynamotor\Helpers\ResourceHelper;

// Base Controller class for PostHelper
// contains default behaviour of 
class BasePHController extends MY_Controller
{
	var $def_paging_limit = 25;

	var $ph = null;
	var $ph_segment_offset = 1;
	var $ph_is_undefined_section = false;

	function __construct(){
		parent::__construct();
		
		$this->load->helper('datetime');
	}

	protected function _post_search($section, $options = false, $offset=0, $limit = 20){
		
		$ph = $this->ph;
		if(empty($ph)){
			$ph = PostHelper::get_section($section);
		}
		if(empty($ph)){
			return $this->_show_404('section_not_found');
		}
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		
		if(!$options){



			$sorting = array();

			if($ph->config('post_priority_enabled') ){
				$sorting['priority'] = 'asc';
			}

			$sorting ['publish_date'] = 'desc';
			$sorting ['create_date'] = 'desc';

			$options = array(
				'is_live'=>$this->record_is_live,
				'status'=>$this->record_status_code,
				'_date_available'=>time_to_date(),
				'_order_by'=> $sorting,
			);
		}

		if($ph->config('post_sort') !== NULL){
			$options['_order_by'] = $ph->config('post_sort');
		}

		$options['section'] = $section;

		if( $ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}

		$post_result = $ph->find_posts($offset , $limit,$options);
		return $this->_post_search_result($post_result);
	}

	protected function _post_search_result($post_result){

		//log_message('debug', 'Base_PH_Controller/_post_search_result, result='.print_r($post_result,true));

		$posts = array();

		$result = array(
			'data'=> array(),
			'paging'=> array(
				'offset'=>0,
				'limit'=>0,
				'total'=>0,
				'page'=>0,
				'total_page'=>0,
			),
		);

		if(!empty($post_result['data'])){
			foreach($post_result['data'] as $idx=> $post){
				$posts[] = $this->_post_mapping($post);
			}

			$result['data'] = $posts;
			$result['paging']['offset'] = $post_result['index_from'];
			$result['paging']['limit'] = $post_result['limit'];
			$result['paging']['total'] = $post_result['total_record'];
			$result['paging']['page'] = $post_result['page'];
			$result['paging']['total_page'] = $post_result['total_page'];
		}

		return $result;
	}

	protected function _post_list($section,$offset=0,$limit = -1){
		


		$ph = $this->ph;
		
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		if(!$ph->is_listing_enabled){
			return $this->_show_404('listing_not_allowed');
		}
		
		$vals = $this->_get_default_vals('list');

		if($this->uri->is_extension(array('js'))){
			return $this->_render('index.js.php',$vals);
		}


		$is_no_result = false;


		$sorting = array();

		if($ph->config('post_priority_enabled') ){
			$sorting['priority'] = 'asc';
		}

		$sorting ['publish_date'] = 'desc';
		$sorting ['create_date'] = 'desc';

		$options = array(
			'is_live'=>$this->record_is_live,
			'status'=>$this->record_status_code,
			'_date_available'=>time_to_date(),
			'_order_by'=>$sorting,
		);

		if($ph->config('post_sort')!== NULL){
			$options['_order_by'] = $ph->config('post_sort');
		}

		if($offset < 0) $offset = 0;
		if($limit < 1) $limit = $this->def_paging_limit;

		if($this->input->get_post('offset') != NULL){
			$offset = intval($this->input->get_post('offset'));
			if($offset < 1 || !is_int($offset)){
				$offset = 0;
			}
		}

		if($this->input->get_post('limit') != NULL){
			$limit = intval($this->input->get_post('limit'));
			if($limit < 1 || !is_int($limit)){
				$limit = 1;
			}
			if($limit > 100){
				$limit = 100;
			}
		}

		if($this->input->get_post('page') != NULL){
			$page = intval($this->input->get_post('page'));
			if($page < 1 || !is_int($page)){
				$page = 1;
			}

			$offset = ($page - 1) * $limit;
		}

		if($ph->is_category_enabled){

			$req_category = $this->input->get_post('category');

			if(!empty($req_category)){
				$req_category_row = $ph->category_model->read(array('_mapping'=>$req_category,'status'=>$this->record_status_code,'is_live'=>$this->record_is_live));
				if(empty($req_category_row['id'])){
					$is_no_result = true;
					log_message('error','Portal_PH_Controller/_post_list#category does not live (id='.$req_category.')');

				}else{
					//$options['_with_category'] = $req_category_row['id'];
					$options['_with_category'] = $req_category_row['id'];
				}
			}
		}

		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}
		
		$post_result = $ph->find_posts($offset , $limit, $options);
		$vals['posts'] = $this->_post_search_result($post_result);


		if($this->_is_ext('data')){
			return $this->_api($vals['posts']);
		}

		if($this->_is_ext('html')){
			return $this->_render('index',$vals);
		}
		
		return $this->_show_404('unmatched_extension');
	}
	
	protected function _post_tag($section,$tag=''){

		if(empty($tag)){
			return $this->_show_404('tag_is_empty');
		}
		
		$ph = $this->ph;
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		if(!$ph->is_tag_enabled){
			return $this->_show_404('tag_not_allowed');
		}
		
		
		
		$vals = $this->_get_default_vals('tag');

		if($this->uri->is_extension(array('js'))){
			return $this->_render('tag.js',$vals);
		}

		$options = array('_mapping'=>urldecode($tag),'status'=>$this->record_status_code,'is_live'=>$this->record_is_live);
		/*
		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}
		//*/
		$tag_row = $ph->tag_model->read($options);
		if(!isset($tag_row['id']))
			return $this->_show_404('record_not_found');

		$vals['current_tag'] = $tag_row;

		$tag_ids [] = $tag_row['id'];




		$sorting = array();

		if($ph->config('post_priority_enabled') ){
			$sorting['priority'] = 'asc';
		}

		$sorting ['publish_date'] = 'desc';
		$sorting ['create_date'] = 'desc';

		$options = array(
			'tag_ids'=>$tag_ids,
			'is_live'=>$this->record_is_live,
			'status'=>$this->record_status_code,
			'_date_available'=>time_to_date(),
			'_order_by'=>$sorting,
		);

		if($ph->config('post_sort')!==NULL){
			$options['_order_by'] = $ph->config('post_sort');
		}


		$offset = 0;
		$limit = $this->def_paging_limit;

		if($this->input->get_post('offset') != NULL){
			$offset = intval($this->input->get_post('offset'));
			if($offset < 1 || !is_int($offset)){
				$offset = 0;
			}
		}

		if($this->input->get_post('limit') != NULL){
			$limit = intval($this->input->get_post('limit'));
			if($limit < 1 || !is_int($limit)){
				$limit = 1;
			}
			if($limit > 100){
				$limit = 100;
			}
		}

		if($this->input->get_post('page') != NULL){
			$page = intval($this->input->get_post('page'));
			if($page < 1 || !is_int($page)){
				$page = 1;
			}

			$offset = ($page - 1) * $limit;
		}


		/*
		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
		}
//*/
		$category_row = $ph->tree_data($this->uri->segments, $offset);
		$child_category_ids = $ph->find_child_category_ids($category_row['id']);

		$post_result = $ph->find_posts($offset , $limit, $options);
		$vals['posts'] = $this->_post_search_result($post_result);

		if($this->_is_ext('data')){

			$_vals = array_merge($this->_tag_mapping($tag_row), $vals['posts']);
			return $this->_api($_vals);
		}

		if($this->_is_ext('html')){
			return $this->_render('tag',$vals);
		}


		return $this->_show_404('unmatched_extension');
	}

	protected function _post_category($section,$offset=1){
		
		$ph = $this->ph;
		if(empty($ph)){
			$ph = PostHelper::get_section($section);
		}
		if(empty($ph)){
			return $this->_show_404('section_not_found');
		}
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		if(!$ph->is_category_enabled){
			return $this->_show_404('category_not_allowed');
		}
		
		
		$vals = $this->_get_default_vals('category');

		if($this->uri->is_extension(array('js'))){
			return $this->_render('category.js',$vals);
		}



		$total = count($this->uri->segments);
		$cat_rst = $ph->tree_data($this->uri->segments,$offset,$total);
		if(empty($cat_rst['id'])){
			return $this->_show_404('category_not_found');
		}
		


		$sorting = array();

		if($ph->config('post_priority_enabled') ){
			$sorting['priority'] = 'asc';
		}

		$sorting ['publish_date'] = 'desc';
		$sorting ['create_date'] = 'desc';

		$options = array(
			'is_live'=>$this->record_is_live,
			'status'=>$this->record_status_code,
			'_date_available'=>time_to_date(),
			'_order_by'=>$sorting,
		);
	
		$child_ids = $ph->find_child_category_ids($cat_rst['id']);
		$options['category_id'] = $child_ids;
		$options['category_id'][] = $cat_rst['id'];

		if($ph->config('post_sort')!==NULL){
			$options['_order_by'] = $ph->config('post_sort');
		}

		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}


		$offset = 0;
		$limit = $this->def_paging_limit;

		if($this->input->get_post('offset') != NULL){
			$offset = intval($this->input->get_post('offset'));
			if($offset < 1 || !is_int($offset)){
				$offset = 0;
			}
		}

		if($this->input->get_post('limit') != NULL){
			$limit = intval($this->input->get_post('limit'));
			if($limit < 1 || !is_int($limit)){
				$limit = 1;
			}
			if($limit > 100){
				$limit = 100;
			}
		}

		if($this->input->get_post('page') != NULL){
			$page = intval($this->input->get_post('page'));
			if($page < 1 || !is_int($page)){
				$page = 1;
			}

			$offset = ($page - 1) * $limit;
		}

		$post_result = $ph->find_posts($offset, $limit, $options);
		$vals['posts'] = $this->_post_search_result($post_result);
		
		$options = array(
			'is_live'=>$this->record_is_live,
			'status'=>$this->record_status_code,
			'id'=> $cat_rst['id'],
			'_date_available'=>time_to_date(),
		);

		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}
		$vals['breadcrumb'] = $cat_rst['breadcrumb'];
		$vals['current_category'] = $ph->category_model->read($options);

		if($this->_is_ext('data')){
			return $this->_api(($vals['posts']));
		}

		if($this->_is_ext('html')){
			return $this->_render('category',$vals);
		}
		
		

		return $this->_show_404('unmatched_extension');
	}

	protected function _post_category_tree($section,$seg_offset=1){
		if(!$this->_is_ext('data'))
			return $this->_show_404('unmatched_extension');
		
		$ph = $this->ph;
		if(empty($ph)){
			$ph = PostHelper::get_section($section);
		}
		if(empty($ph)){
			return $this->_show_404('section_not_found');
		}
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		if(!$ph->is_category_enabled){
			return $this->_show_404('category_not_allowed');
		}

		$offset = 0;
		$limit = $this->def_paging_limit;

		if($this->input->get_post('offset') != NULL){
			$offset = intval($this->input->get_post('offset'));
			if($offset < 1 || !is_int($offset)){
				$offset = 0;
			}
		}

		if($this->input->get_post('limit') != NULL){
			$limit = intval($this->input->get_post('limit'));
			if($limit < 1 || !is_int($limit)){
				$limit = 1;
			}
			if($limit > 100){
				$limit = 100;
			}
		}

		if($this->input->get_post('page') != NULL){
			$page = intval($this->input->get_post('page'));
			if($page < 1 || !is_int($page)){
				$page = 1;
			}

			$offset = ($page - 1) * $limit;
		}

		$rst = $ph->tree_data($this->uri->segments,$seg_offset,-1, $this->lang->locale());
		
		if(!empty($rst['id'])){
			$child_ids = $ph->find_child_category_ids($rst['id']);

			$options = array(
				'is_live'=>$this->record_is_live,
				'status'=>$this->record_status_code,
				'_date_available'=>time_to_date(),
			);
			$options['category_id'] = $child_ids;
			$options['category_id'][] = $rst['id'];
		

			if($ph->is_localized){
				$options['_with_locale'] = $this->lang->locale();
				//$options['_with_locale_prefix'] = '';
			}


			$options['_order_by'] = array('publish_date'=>'desc','create_date'=>'desc');
			$post_result = $ph->find_posts($offset,$limit,$options);
			
			$rst['posts'] = $this->_post_search_result($post_result);
		}else{
			$rst['offset'] = $offset;
			$rst['segments'] = $this->uri->segments;
		}
		return $this->_api($rst);
	}

	protected function _post_view($section,$_mapping=FALSE,$return = FALSE){
		
		$ph = $this->ph;
		if(empty($ph)){
			$ph = PostHelper::get_section($section);
		}
		if(empty($ph)){
			if($return) return NULL;
			return $this->_show_404('section_not_found');
		}
		if($ph->config('enabled') === FALSE || $ph->config('enabled') == 'no'){
			return $this->_show_404('section_disabled');
		}
		
		$vals = $this->_get_default_vals('view');
		
		$options= array(
			'section'=>$section,
			'is_live'=>$this->record_is_live,
			'status'=>array('1','2'),
			'_date_available'=>time_to_date(),
			'_mapping'=>urldecode($_mapping), 
		);

		if($ph->is_localized){
			$options['_with_locale'] = $this->lang->locale();
			//$options['_with_locale_prefix'] = '';
		}
		$post = $ph->post_model->read($options);
		if(empty($post['id'])){
			return $this->_show_404('no_matched_record');
		}
		$section_prefix = ($ph->is_default) ? '/' : $section.'/';
		$post['url'] = site_url($section_prefix.$post['_mapping']);
		$vals['post_record'] = $ph->post_mapping_row($post);
		$vals['post'] = $this->_post_mapping($vals['post_record']);

		/*
		// Duplicated query.
		if($ph->is_tag_enabled){
			$post_tag_ids = $ph->post_model->get_tags($post['id'],'1');


			//$vals['post']['tags'] = array();
			if(!empty($post_tag_ids)){
				$options = array('id'=>$post_tag_ids,'is_live'=>'1','status'=>'1','_date_available'=>time_to_date());
				$post_tags = $ph->tag_model->find($options);
				$vals['post']['tags'] = $post_tags;
			}
		}
		//*/

		if($return) return $vals;
		return $this->_post_view_render($vals);
	}

	protected function _init_meta($vals){
		parent::_init_meta($vals);

		if(!empty($vals['post'])){

			$this->asset->set_meta_property('og:site_name', $this->config->item('site_name'));
			$this->asset->set_meta_content('twitter:site_name', $this->config->item('site_name'));

			$this->asset->set_meta_property('og:title', $vals['post']['title']);
			$this->asset->set_meta_content('twitter:title', $vals['post']['title']);

			$this->asset->set_meta_property('og:description', $vals['post']['description']);
			$this->asset->set_meta_content('twitter:description', $vals['post']['description']);
			$this->asset->set_meta_content('description', $vals['post']['description']);

			$url = $vals['post']['url'];
			if(! preg_match('#^https?\:\/\/#', $url ) ) $url  = base_url($url );
			$this->asset->set_meta_property('og:url', $url);

			if(!empty($vals['post']['cover']['thumbnail'])){
				$image_url = $vals['post']['cover']['thumbnail']['url'];
				if(! preg_match('#^https?\:\/\/#', $image_url ) ) $image_url  = base_url($image_url );
				$this->asset->add_meta_property('og:image', $image_url);
				$this->asset->add_meta_content('twitter:image', $image_url );
			}
		}
	}

	protected function _post_view_render($vals){

		if($this->uri->is_extension(array('js'))){
			return $this->_render('view.js',$vals);
		}

		if($this->_is_ext('html')){
			return $this->_render('view',$vals);
		}

		return $this->_show_404('no_matched_extension');
	}

	protected function _shorten_text($val, $length=250, $tail = '...', $encoding = 'UTF-8'){
		$val = html_entity_decode($val);
		$val = str_replace('<', '', $val);
		$size = mb_strlen($val, $encoding);
		if($size > $length){
			return mb_substr($val, 0, $length - mb_strlen($tail), $encoding). $tail;
		}
		return $val;
	}

	protected function _post_mapping($raw_row,$options=false){

		$ph = PostHelper::get_section($raw_row['section']);

		if(empty($ph)){
			show_error('Base_PH_Controller/_post_mapping, section does not matched from config: '. $raw_row['section']);
		}

		$load_cover = !isset($options['_no_cover']) || !$options['_no_cover'];
		$load_gallery = !isset($options['_no_gallery']) || !$options['_no_gallery'];

		if($this->_is_debug()){
			$row = $raw_row;
		}else{	
			$row = array();
			$row['id'] = $raw_row['id'];
			$row['section'] = $raw_row['section'];
			$row['_mapping'] = $raw_row['_mapping'];
			$row['slug'] = $raw_row['slug'];
			$row['title'] = $raw_row['title'];
			$row['description'] = $raw_row['description'];
			$row['content'] = $raw_row['content'];
		}
		if(!isset($raw_row['path'])){
			$row['path'] = $raw_row['section'].'/'.$raw_row['_mapping'];

			if($this->config->item('ph_section_default') == $raw_row['section']){
				$row['path'] = $raw_row['_mapping'];
			}
		}else{
			$row['path'] = $raw_row['path'];
		}

		$row['url'] = web_url($row['path']);
		$row['loc_url'] = site_url($row['path']);

		$row['publish_date'] = $raw_row['publish_date'];
		$row['publish_date_ts'] = strtotime($raw_row['publish_date']) * 1000;
		$row['plain'] = $raw_row['plain'] == '1' ? true : false;

		$post_cover_size_group = ($ph->config('post_cover_size_group'))!==NULL ? $ph->config('post_cover_size_group') : 'file';
		$post_album_size_group = ($ph->config('post_album_size_group'))!==NULL ? $ph->config('post_album_size_group') : 'file';


		$post_cover_images = ($ph->config('post_cover_images'))!==NULL ? $ph->config('post_cover_images') : array('thumb'=>'thumb','large'=>'large','src'=>'source');
		$post_album_images = ($ph->config('post_album_images'))!==NULL ? $ph->config('post_album_images') : array('thumb'=>'thumb','large'=>'large','src'=>'source');

		if($this->_is_debug()){
			$row['_raw'] = $raw_row;
			$row['_load_cover'] = $load_cover;
			$row['_allow_cover'] = $ph->config('cover_enabled') !== FALSE;
			$row['_load_gallery'] = $load_gallery;
			$row['_allow_gallery'] = $ph->config('album_enabled') !== FALSE;
		}
		if($load_cover){
			if($ph->config('cover_enabled') !== FALSE){

				if(!empty($raw_row['cover_row'])){



					if(!empty($post_cover_images) && is_array($post_cover_images)){
						$picture = array();
						foreach($post_cover_images as $attr_name => $size_name){
							$_picture = $this->_post_picture_mapping($raw_row,$raw_row['cover_row'],$post_cover_size_group,$size_name,'cover');
							if(!empty($_picture))
								$picture[ $attr_name ] = $_picture;
						}
						$row['cover'] = $picture;
					}


				}
			}
		}

		if($load_gallery){
			if($ph->config('album_enabled') !== FALSE){
				$row['gallery'] = array();
				//$row['album_row'] = $raw_row['album_row'];;

				if(!empty($raw_row['album_row']['photos'])){

					foreach($raw_row['album_row']['photos'] as $photo_row){
						$picture = array();
						$picture['parameters'] = $photo_row['parameters'];
						if(  is_array($post_album_images)){
							foreach($post_album_images as $attr_name => $size_name){
								$_picture = $this->_post_picture_mapping($raw_row,$photo_row,$post_album_size_group,$size_name,'album');
								if(!empty($_picture))
									$picture[ $attr_name ] = $_picture;
							}
						}
						$row['gallery'] [] = $picture;

					}
				}
			}
		}

		if($ph->is_localized && !empty($raw_row['locale'])){
			$row['locale'] = $raw_row['locale'];
		}

		if($ph->is_category_enabled){

			if(isset( $raw_row['categories']) && is_array($raw_row['categories'])){
				foreach($raw_row['categories'] as $cat_row){
					$row['categories'][] = $this->_category_mapping($cat_row);
				}
			}

			if(!empty($raw_row['category_id'])){
				$extra_options = array();
				if($ph->is_localized && !empty($raw_row['locale'])){
					$extra_options['_with_locale'] = $raw_row['locale'];
				}
				$cur_category = $ph->get_category($raw_row['category_id'], $raw_row['is_live'], $raw_row['status'], $extra_options);

				$cat_rst = $ph->path_data($cur_category['id_path'].'/'.$cur_category['id']);
				$row['breadcrumb'] = $cat_rst['breadcrumb'];
				if(!empty( $cur_category['parent_id'])){

					// Fetch all category data from a path. 
					// Example: '/trend/kpop' => 
					// array( 
					//     array('id'=>'1', ...), 
					//     array('id'=>'2', ...),
					//     ...
					// )

					if(!empty( $cat_rst['breadcrumb'][0])){
						$row['root_category'] = $this->_category_mapping($cat_rst['breadcrumb'][0]);
					}
					if(!empty( $cat_rst['categories'][0])){
						$row['root_category'] = $this->_category_mapping($ph->breadcrumb_format($cat_rst['categories'][0]));
					}
				}else{
					//$row['breadcrumb'][] = $ph->breadcrumb_format($cur_category);
					$row['root_category'] = $ph->breadcrumb_format($cur_category);
				}
			}
			//*/
		}
		if($ph->is_tag_enabled && isset($raw_row['tags'])){
			$row['tags'] = array();
			foreach($raw_row['tags'] as $tag_row){
				$row['tags'] [] = $this->_tag_mapping($tag_row);
			}
		}

		if($this->ph->config('post_parameters') !== NULL && is_array($this->ph->config('post_parameters'))){
			if(isset($raw_row['parameters'])){
				foreach($this->ph->config('post_parameters') as $field_name => $field_info){

					if (isset($raw_row['parameters'][ $field_name ]))
						$row['parameters'][ $field_name ] = $raw_row['parameters'][ $field_name ];
				
				}

			}
			if(isset($raw_row['loc_parameters'])){

				if(is_array($this->ph->config('post_parameters'))){
					foreach($this->ph->config('post_parameters') as $field_name => $field_info){
						if(isset($field_info['is_localized']) && $field_info['is_localized']){
							if (isset($raw_row['loc_parameters'][ $field_name ]))
								$row['parameters'][ $field_name ] = $raw_row['loc_parameters'][ $field_name ];
						}
					}
				}
			}
		}

		$row['_raw'] = $raw_row;

		$row['description'] = isset($raw_row['description']) ? $raw_row['description'] : '';
		if(isset($raw_row['locale'])){
			$row['locale'] = $raw_row['locale'];
		}

		if(isset($raw_row['loc_title']) && $raw_row['loc_status'] == '1'){
			$row['def_title'] = $raw_row['title'];
			$row['title'] = $raw_row['loc_title'];
			$row['loc_title'] = $raw_row['loc_title'];
		}
		if(isset($raw_row['loc_description']) && $raw_row['loc_status'] == '1'){
			$row['def_description'] = $raw_row['description'];
			$row['description'] = $raw_row['loc_description'];
			$row['loc_description'] = $raw_row['loc_description'];
		}
		$row['description_short'] = $this->_shorten_text($raw_row['description']);

		$row['content'] = isset($raw_row['content']) ? $raw_row['content'] : '';

		if(isset($raw_row['loc_content']) && $raw_row['loc_status'] == '1' ){
			$row['def_content'] = $raw_row['content'];
			$row['content'] = $raw_row['loc_content'];
			$row['loc_content'] = $raw_row['loc_content'];
		}

		$row['content_short'] = $this->_shorten_text(strip_tags($raw_row['content']));
		

		return $row;
	}

	protected function _category_mapping($raw_row,$options=false){
		$row = array(
			'id'=>$raw_row['id']
		);

		$row['section'] = data('section', $raw_row);
		if(empty($row['section']) && isset($this->ph->section)) $row['section'] = $this->ph->section;
			$row['_mapping'] = $raw_row['_mapping'];
		$row['slug'] = data('slug', $raw_row);
		if(!empty($raw_row['locale']))
			$row['locale'] = $raw_row['locale'];

		$row['title'] = $raw_row['title'];
		if(!empty($raw_row['loc_title']))
			$row['title'] = $raw_row['loc_title'];

		if(!empty($raw_row['description']))
			$row['description'] = $raw_row['description'];
		if(!empty($raw_row['loc_description']))
			$row['description'] = $raw_row['loc_description'];

		$row['content'] = data('content', $raw_row);
		if(!empty($raw_row['loc_content']))
			$row['content'] = $raw_row['loc_content'];

		if(isset($raw_row['publish_date'])){
			if(substr($raw_row['publish_date'],0,4)!='0000'){
				$row['publish_date'] = $raw_row['publish_date'];
				$row['publish_date_ts'] = strtotime($raw_row['publish_date']) * 1000;
			}
		}
		$row['parameters'] = data('parameters',$raw_row);
		$row['path'] = $row['section'].'/category/'.$raw_row['_mapping'];
		$row['url'] = site_url($row['path']);

		return $row;
	}

	protected function _tag_mapping($raw_row,$options=false){
		$row = array(
			'id'=>$raw_row['id']
		);

		$row['section'] = $raw_row['section'];
			$row['_mapping'] = $raw_row['_mapping'];
		$row['slug'] = $raw_row['slug'];
		if(!empty($raw_row['locale']))
			$row['locale'] = $raw_row['locale'];

		$row['title'] = $raw_row['title'];
		if(!empty($raw_row['loc_title']))
			$row['title'] = $raw_row['loc_title'];

		if(isset($raw_row['publish_date'])){
			if(substr($raw_row['publish_date'],0,4)!='0000'){
				$row['publish_date'] = $raw_row['publish_date'];
				$row['publish_date_ts'] = strtotime($raw_row['publish_date']) * 1000;
			}
		}
		$row['path'] = $raw_row['section'].'/tag/'.$raw_row['_mapping'];
		$row['url'] = site_url($row['path']);

		return $row;
	}

	protected function _post_picture_mapping($post,$file_row,$group='file',$size='thumb',$subpath = false,$options = false,&$image_info=false){


		if(is_array($file_row) && !isset($file_row['id'])){

			if($this->_is_debug())
				log_message('debug','Base_PH_Controller/_post_picture_mapping:'.$post['section'].'@'.$post['id'].', array of images='.print_r($file_row, true));

			$outputs = array();
			foreach($file_row as $file){
				$_image_info = false;
				$image_output = $this->_post_picture_mapping($post, $file, $group,$size,$subpath,$options,$_image_info);
				if(!empty($image_output)){
					$outputs[] = $image_output;
					$image_info[] = $_image_info;
				}
			}
			return $outputs;
		}

		if(empty($options)) $options = array('rebuild'=>$this->is_refresh);

		$ph = PostHelper::get_section($post['section']);
		$post_id = $post['id'];

		$dest_path = $ph->section;
		$dest_path.='/'.$post_id;
		$dest_path.='/photos';
		if(!empty($subpath))
			$dest_path.='/'.$subpath;

		$subpath = explode('/', $dest_path);
		
		if($this->_is_debug())
			log_message('debug','Base_PH_Controller/_post_picture_mapping:'.$post['section'].'@'.$post['id'].', build image for '.$file_row['id'].' at '.$dest_path.'. imagegroup='.$group.', size='.$size);

		return $this->_picture_mapping($file_row, $group, $size, $subpath, $options, $image_info);
	}

	protected function _post_file_url($post, $file_row){

		//log_message('debug','LH_Controller/_ph_get_upload_url:'.$post['section'].'@'.$post['id']);

		// generate user unique location
		$path = $post['section'].'/'.$post_id.'/post_content';

		$url = $this->resource->upload_url($file_row,$this->is_refresh,'files',explode('/',$path));

		return $url;
	}


	public function _render($view, $vals = false, $layout = false, $theme = false) {

		if(!empty($this->ph)){
			$ph = $this->ph;

			$section = $ph->section;
			$vals['section'] = $ph->section;

			$sections = $this->config->item('ph_sections');
			if(!$layout && !empty($sections[$ph->section]['layout'])){
				$layout = $sections[$ph->section]['layout'];
			}

			if($ph->is_tag_enabled){
				$options = array(
					'is_live'=>$this->record_is_live,
					'status'=>$this->record_status_code,
					'_date_available'=>time_to_date(),
					'_order_by'=>array('title'=>'asc'),
				);
				/*
				if($ph->is_localized){
					$options['_with_locale'] = $this->lang->locale();
					//$options['_with_locale_prefix'] = '';
				}
				//*/
				$r_records = $ph->tag_model->find($options);
				$vals['section_tags'] = array();
				if(!empty($r_records) && is_array($r_records)){
					foreach($r_records as $r_record){
						$vals['section_tags'][] = $this->_category_mapping($r_record);
					}
				}
			}
			if($ph->is_category_enabled){
				$options = array(
					'is_live'=>$this->record_is_live,
					'status'=>$this->record_status_code,
					'parent_id'=>'0',
					'_date_available'=>time_to_date(),
					'_order_by'=>array('title'=>'asc'),
				);
				if($ph->is_localized){
					$options['_with_locale'] = $this->lang->locale();
					//$options['_with_locale_prefix'] = '';
				}
				$r_records = $ph->category_model->find($options);
				$vals['section_categories'] = array();
				if(!empty($r_records) && is_array($r_records)){
					foreach($r_records as $r_record){
						$vals['section_categories'][] = $this->_category_mapping($r_record);
					}
				}
			}

			//if(substr($view,-4,4)!='.php') $view.='.php';

			$theme = $this->response->get_theme();

			if(file_exists(VIEWPATH.'themes/'.$theme.'/'.$section.'/'.$view.'.php')){
				$view = $section .'/'.$view;
			}elseif(file_exists(VIEWPATH.'themes/'.$theme.'/ph/'.$view.'.php')){
				$view = 'ph/'.$view;
			}elseif(file_exists(VIEWPATH.''.$section.'/'.$view.'.php')){
				$view = $section .'/'.$view;
			}elseif(file_exists(VIEWPATH.'ph/'.$view.'.php')){
				$view = 'ph/'.$view;
			}
		}
		return parent::_render($view, $vals, $layout, $theme);
	}
}
