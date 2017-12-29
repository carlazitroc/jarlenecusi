<?php 

use Dynamotor\Helpers\Numbertowords;

if(!defined('DYNAMATOR_ADMIN_THEME_SBADMIN')){
	define('DYNAMATOR_ADMIN_THEME_SBADMIN', TRUE);

	function main_menu_selected($tree){
		global $CFG;
		$_selected_tree = $CFG->item('main_menu_selected');
		if(is_array($_selected_tree)){

			if(is_array($tree)){
				if(count($_selected_tree)<1){
					return FALSE;
				}
				for($i = 0; $i< count($_selected_tree) && $i < count($tree) ; $i++){
					//for($k = 0; $k < count($tree); $k++){
						if($_selected_tree[$i] != $tree[$i]){
							return FALSE;
						}
					//}
				}
			}elseif($_selected_tree[0] != $tree) {
				return FALSE;
			}
			return TRUE;
		}elseif(is_string($_selected_tree)){
			
			if(is_array($tree)){
				if(count($tree)<1){
					return FALSE;
				}
				if($_selected_tree != $tree[0]){ 
					return FALSE;
				}
			}elseif($_selected_tree != $tree){ 
				return FALSE;
			}
			return TRUE;
		}
		return FALSE;
	}

	function main_menu_status($tree, $base_classes=NULL){
		$status = main_menu_selected($tree);
		if($status){
			if(empty($base_classes)){
				$base_classes = array();
			}
			$base_classes[] = 'active';
		}
		if(!empty($base_classes)){
			return ' class="'.implode(" ", $base_classes).'"'; 
		}
		return '';
	}

	function acl_has_perm_str($perm_str){
		$conds = explode("||",trim($perm_str));
		foreach($conds as $idx => $cond){
			$cond_perms = explode(",",$cond);
			foreach($cond_perms as $idx => $cond_perm){
				if(!acl_has_permission($cond_perm)){
					return FALSE;
				}
			}
		}
		return TRUE;
	}

	global $CFG;
	$main_menu_selected = $CFG->item('main_menu_selected');
	function main_menu_create ($menus,$offset=1, $level = 1, $parent_selected = -1){
		global $main_menu_selected;
		$spacer = "\t";
		$prefix = str_repeat($spacer, $level + $offset - 1);
		$str = '';
		
		$menu_level = $level - 1;

		foreach($menus as $idx => $item){
			//$str.= '<!-- ' . print_r($item,true).' -->'."\n";
			if(!empty($item['perms'])){
				if(!acl_has_perm_str($item['perms'])){
					continue; // skip this item
				} 
			}
			
			$has_subitems = isset($item['subitems']) && count($item['subitems'])>0;
			
			$text = '';
			if(isset($item['text'])) 
				$text = lang($item['text']);
			if(isset($item['icon'])) 
				$text = '<i class="'.($item['icon']).'"></i> '.$text;
			if($has_subitems) 
				$text.= ' <span class="fa arrow"></span>';
			
			$str.= $prefix."<li";

			$selected = 0;
			if(isset($item['tree']))
				$str.= main_menu_status($item['tree']);

			$str.="><a href=\"".site_url($item['url'])."\">$text</a>";
			if($has_subitems>0){
				$level_str = 'nav-'.strtolower(Numbertowords::convert_number($level+1,'first')).'-level';
				$str.= "\n";
				$str.= $prefix.$spacer.'<ul class="nav '.$level_str.' collapse">'."\n";
				$str.= main_menu_create($item['subitems'],$offset+1,$level+1, $selected);
				$str.= $prefix.$spacer."</ul>";
			}
			$str.= "</li>\n";
			
		}
		return $str;
	}

}