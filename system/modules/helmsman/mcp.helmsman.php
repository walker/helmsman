<?php

	class Helmsman_CP {
		var $version = '0.1';
		var $top_level_lock = false; //change to true to not allow the user to modify the top level of the menu
		
		function Helmsman_CP($switch=true)
		{
			global $IN;
			
			if ($switch)
			{
				switch($IN->GBL('P'))
				{
					case 'delete':
						$this->delete_nav_item();
						break;
					case 'add':
						$this->modify_nav_item();
						break;
					default:
						$this->helmsman_home();
						break;
				}
			}
		}
		
		function helmsman_home()
		{
			global $DSP, $LANG, $PREFS;
			
			if(isset($_POST['data']) && !empty($_POST['data']))
			{
				$the_data = $_POST['data'];
				$the_data = array_merge($the_data);
				
				// echo '<pre>';
				// print_r($the_data);
				// echo '</pre>';
				// exit();
				
				//save it
				$this->navigation_save($the_data);
			}
			
			$DSP->title = $LANG->line('helmsman_module_name');
			$DSP->crumb = $DSP->anchor(BASE.
										AMP.'C=modules'.
										AMP.'M=helmsman',
			$LANG->line('helmsman_module_name'));
			$DSP->crumb .= $DSP->crumb_item($LANG->line('helmsman_menu')); 
			
			$DSP->body .= $DSP->heading($LANG->line('helmsman_menu'));
			
			$open_form = $DSP->form_open(
				array(
					  'action'	=> 'C=modules'.AMP.'M=helmsman', 
					  'method'	=> 'post',
					  'name'	=> 'nav_form',
					  'id'		=> 'nav_form'
					 )
			 );
			
			$navigation_items = $this->get_navigation_array();
			
			// echo '<pre>';
			// print_r($navigation_items);
			// echo '</pre>';
			// exit();
			
			$counter = 0;
			$depth = 0;
			
			// print_r($GLOBALS);
			// exit();
			
			$DSP->extra_css = $_SERVER['DOCUMENT_ROOT'].'/'.$PREFS->default_ini['system_folder'].'/modules/helmsman/css/styles.css';
			
			$DSP->body .= '<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/jquery-1.3.1.min.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/interface_1_2/interface.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/inestedsortable-1.0.1.pack.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/scripts.js"></script>'.
			'<script type="text/javascript">
				function returnNewItem(counter, the_level) {
					var item_string = "<li class=\'sortable-navitem\'><div class=\'hidden\'><input type=\'hidden\' name=\'data["+counter+"][link_depth]\' value=\'"+the_level+"\' /></div><div class=\'example-link\'><a href=\'\'></a></div><input  dir=\'ltr\'  style=\'width:200px;margin-left:250px;\' type=\'text\' name=\'data["+counter+"][link_title]\' id=\'data"+counter+"link_title\' value=\'\' size=\'50\' maxlength=\'255\' class=\'leftmost input\' /> <input  dir=\'ltr\'  style=\'width:200px\' type=\'text\' name=\'data["+counter+"][link_url]\' id=\'data"+counter+"link_url\' value=\'\' size=\'50\' maxlength=\'255\' class=\'input\'  /> <a href=\'javascript:void(0);\' title=\'Delete\' class=\'delete-link\'><img src=\'/themes/cp_global_images/delete.png\' alt=\'Delete\' title=\'Delete\' /></a><a href=\'javascript:void(0);\' title=\'Move\' class=\'handlebar\'><img src=\'/console/modules/helmsman/img/draggable.gif\' /></a></li>";
					return item_string;
				}
			</script>'.
				$DSP->qdiv('longWrapper',
					$DSP->qdiv('add-new-item', '<a href="javascript:void(0);">Add another navigation item.</a>').
					$open_form.
					$this->table_rows($navigation_items, $counter, $depth)
				);
			
			// $DSP->body .= $DSP->qdiv('itemWrapper', $DSP->heading($DSP->anchor(BASE.
			// 																	AMP.'C=modules'.
			// 																	AMP.'M=helmsman'.
			// 																	AMP.'P=add',
			// 																	$LANG->line('add_nav_item')),
			// 																	5));
			// 
			// $DSP->body .= $DSP->qdiv('itemWrapper', $DSP->heading($DSP->anchor(BASE.
			// 																	AMP.'C=modules'.
			// 																	AMP.'M=helmsman'.
			// 																	AMP.'P=modify',
			// 																	$LANG->line('modify_nav_item')),
			// 																	5));
		}
		
		function incoming_entities($text)
		{
			return preg_replace("/&(?![#a-z0-9]+;)/i", "x%x%", $text);
		}
		
		function encode_entities($text)
		{
			return (function_exists('mb_encode_numericentity'))
			?	 $this->encode_high($text)
			:	 htmlentities($text, ENT_NOQUOTES, "utf-8");
		}
		
		function encode_high($text, $charset = "UTF-8")
		{
			return mb_encode_numericentity($text, $this->cmap(), $charset);
		}
		
		function cmap()
		{
			$f = 0xffff;
			$cmap = array(
				0x0080, 0xffff, 0, $f);
			return $cmap;
		}
		
		function get_navigation_array() {
			global $DB;
			
			$result = $DB->query("SELECT parent_id FROM `exp_helmsman` WHERE parent_id<>0 GROUP BY parent_id");
			
			$top_level = $DB->query("SELECT * FROM `exp_helmsman` WHERE parent_id=0 ORDER BY sequence ASC");
			
			return $this->get_navigation_iterator($top_level->result);
		}
		
		function get_navigation_iterator($results) {
			global $DB;
			
			$navigation = array();
			
			foreach($results as $nav_item) {
				$navigation[$nav_item['id']] = array(
					"title" => $nav_item['title'],
					"html_title" => $nav_item['html_title'],
					"url" => $nav_item['url'],
				);
				$children = $DB->query("SELECT * FROM `exp_helmsman` WHERE parent_id=".$nav_item['id']." ORDER BY sequence ASC");
				if(count($children->result)>0) {
					$navigation[$nav_item['id']]['children'] = $this->get_navigation_iterator($children->result);
				}
			}
			return $navigation;
		}
		
		function table_rows($sections, &$counter, $depth) {
			global $DSP, $PREFS;
			
			if($depth==0) {
				$return = $DSP->qdiv('top-labels', $DSP->qdiv('title-label', 'Title').$DSP->qdiv('link-label', 'Link')).
					"\r\n".
					'<ol id="master-list">'.
					"\r\n";
			} else {
				$return = '<ol class="sub-list" id="sub'.$counter.'list">'."\r\n".
					"\r\n";
			}
			
			$section_counter = 1;
			$section_total = count($sections);
			
			foreach($sections as $key => $section) {
				if(!mb_check_encoding($section['title'], 'utf-8')) {
					$section['title'] = mb_convert_encoding($section['title'], 'utf-8');
				}
				
				if($counter%2==1) {
					$extra_class = ' alt';
				} else {
					$extra_class = '';
				}
				
				$return .= '<li class="';
				if(!$this->top_level_lock || ($this->top_level_lock && $depth>0)) { $return .= 'sortable-navitem'; }
				$return .= $extra_class.'">'."\r\n".
				$DSP->input_hidden('data['.$counter.'][link_depth]', $depth)."\r\n".
				'<div class="example-link"><a href="'.substr($PREFS->core_ini['site_url'], 0, -1).$section['url'].'">'.$section['html_title'].'</a></div>'.
				$DSP->input_text('data['.$counter.'][link_title]', $section['title'], '50', '255', 'leftmost input', '200px', $this->input_extras($depth)).
				$DSP->input_text('data['.$counter.'][link_url]', $section['url'], '50', '255', 'input', '200px', $this->input_extras($depth));
				if(!$this->top_level_lock || ($this->top_level_lock && $depth>0)) {
					$return .= $DSP->anchor('javascript:void(0);', '<img src="/themes/cp_global_images/delete.png" alt="Delete" title="Delete" />', 'title="Delete" class="delete-link"').
					$DSP->anchor('javascript:void(0);', '<img src="/console/modules/helmsman/img/draggable.gif" />', 'title="Move" class="handlebar"');
				}
				$counter++;
				if(isset($section['children']) && count($section['children'])>0) {
					$pass_depth = $depth+1;
					$return .= $this->table_rows($section['children'], $counter, $pass_depth)."\r\n";
				}
				$return .= '</li>'."\r\n";
				$section_counter++;
			}
			if($depth==0) {
				$return .= '</ol>'.
					'<div id="serialized"><textarea style="display:none;" name="serialized_data" id="serialized_data">&nbsp;</textarea></div>'.
					$DSP->input_submit('Save').
					$DSP->form_close().
					'<div id="current-count" style="display:none;">'.$counter.'</div>';
			} else {
				$return .= '</ol>'."\r\n";
			}
			return $return;
		}
		
		function input_extras($depth) {
			$return = '';
			if($this->top_level_lock && $depth==0)
			{
				$return .= 'readonly';
			}
			return $return;
		}
		
		function navigation_save($navigation_items)
		{
			global $DB, $PREFS;
			
			$delete_all = $DB->query('DELETE FROM `exp_helmsman`');
			
			$this->save_iterator($navigation_items);
			
			//exit();
			
			return true;
		}
		
		function save_iterator($the_data, $current_depth=0) {
			global $DB, $PREFS;
			
			$counter = 1;
			
			$testing2 = false;
			$testing3 = false;
			
			if(count($the_data)<8) {
				$testing2 = true;
				// print_r($the_data);
			}
			
			foreach($the_data as $key => $value) {
				if(!is_array($value)) {
					$current_parent = $value;
				} else if(isset($value['link_depth'])) {
					if($counter==1 && $current_depth<$value['link_depth']) {
						$current_depth = $value['link_depth'];
					}
					//var_dump($counter);
					if(empty($value['link_depth'])) {
						$value['link_depth'] = 0;
					}
					
					if($value['link_depth']==$current_depth)
					{
						// Encode the html version of the title for proper display (Ampersand, etc)
						$value['html_title'] = $this->encode_entities($this->incoming_entities($value['link_title']));
						$value['html_title'] = str_replace("x%x%", "&#38;", $value['html_title']);

						// Handle properly for display on the page in the input fields (I hope*)
						if(!mb_check_encoding($value['link_title'], 'utf-8')) {
							$value['link_title'] = mb_convert_encoding($value['link_title'], 'utf-8');
						}

						// Set the values to be the column names from the DB (just for referencability)
						$value['title'] = $value['link_title'];
						$value['url'] = $value['link_url'];
						
						$value['slug'] = $this->__slug($value['link_title']);
						
						if(!$this->top_level_lock || ($this->top_level_lock && $current_parent>0)) {
							if(isset($current_parent)) {
								$DB->query('INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", '.$current_parent.', '.$counter.')');
							} else {
								$DB->query('INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", 0, '.$counter.')');
							}
						}
						
						$the_data[$key] = $DB->insert_id;
					}
					$counter++;
				}
			}
			
			$this->unsetter($the_data);
			
			return true;
		}
		
		function unsetter($the_data) {
			if(!empty($the_data))
			{
				$the_data = array_merge($the_data);
				foreach($the_data as $key => $value) {
					if(count($the_data[$key])==1 && isset($the_data[$key+1]) && count($the_data[$key+1])==1) {
						unset($the_data[$key]);
					} else if(count($the_data[$key])==1 && !isset($the_data[$key+1])) {
						unset($the_data[$key]);
					}
				}
				$the_data = array_merge($the_data);
				$this->save_iterator($the_data);
			}
			return true;
		}
		
		function __slug($string)
		{
			$settings = array('separator' => '_', 'length' => 100);
			
			$string = strtolower($string);
			$string = preg_replace('/[^a-z0-9_]/i', $settings['separator'], $string);
			$string = preg_replace('/' . preg_quote($settings['separator']) . '[' . preg_quote($settings['separator']) . ']*/', $settings['separator'], $string);
			
			if (strlen($string) > $settings['length'])
			{
				$string = substr($string, 0, $settings['length']);
			}
			
			$string = preg_replace('/' . preg_quote($settings['separator']) . '$/', '', $string);
			$string = preg_replace('/^' . preg_quote($settings['separator']) . '/', '', $string);
			
			return $string;
		}
		
		function helmsman_module_install()
		{
			global $DB;
			
			$sql[] = "INSERT INTO exp_modules (module_id,
												module_name,
												module_version,
												has_cp_backend)
												VALUES
												(null,
												'Helmsman',
												'$this->version',
												'y')";
			
			$sql[] = "CREATE TABLE IF NOT EXISTS `exp_helmsman` (
												`id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
												`title` VARCHAR(255) NOT NULL,
												`html_title` VARCHAR(255) NOT NULL,
												`slug` VARCHAR( 255 ) NOT NULL,
												`url` VARCHAR(255) NOT NULL,
												`parent_id` INT(6) UNSIGNED NOT NULL DEFAULT '0',
												`sequence` INT(6) UNSIGNED NOT NULL,
												PRIMARY KEY (`id`),
												INDEX (  `parent_id` ));";
			
			foreach($sql as $query)
			{
				$DB->query($query);
			}
			return true;
		}

		function helmsman_module_deinstall()
		{
			global $DB;
			
			$query = $DB->query("SELECT module_id 
									FROM exp_modules 
									WHERE module_name = 'Helmsman'");
			
			$sql[] = "DELETE FROM exp_module_member_groups 
						WHERE module_id = '".$query->row['module_id']."'";
			
			$sql[] = "DELETE FROM exp_modules 
						WHERE module_name = 'Helmsman'";

			$sql[] = "DELETE FROM exp_modules 
						WHERE module_name = 'Helmsman_CP'";
			
			$sql[] = "DELETE FROM exp_actions 
						WHERE class = 'Helmsman'";
			
			$sql[] = "DELETE FROM exp_actions 
						WHERE class = 'Helmsman_CP'";
			
			$sql[] = "DROP TABLE IF EXISTS exp_helmsman";
			
			foreach ($sql as $query)
			{
			    $DB->query($query);
			}

			return true;
		}
		
	}

?>