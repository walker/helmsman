<?php

	class Helmsman_CP
	{
		//Version of Helmsman Control Panel
		var $version = '1.0.3';
		
		//change to true to not allow the user to modify the top level of the menu
		var $top_level_lock = false;
		
		/**
		 * Constructor function that kicks off the main Helmsman control panel page
		 *
		 * @access	public
		 */
		function Helmsman_CP()
		{
			if(!isset($_GET['M']) || (isset($_GET['M']) && $_GET['M']!='INST')) {
				$this->helmsman_home();
			}
		}
		
		/**
		 * Constructs the Helmsman control panel by modifying the $DSP's title, crumb, and body variables with the data retrieved from the other Helmsman functions
		 *
		 * @access	public
		 */
		function helmsman_home()
		{
			global $DSP, $LANG, $PREFS;
			
			if(isset($_POST['data']) && !empty($_POST['data']))
			{
				$the_data = $_POST['data'];
				$the_data = array_merge($the_data);
				
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
			
			$counter = 0;
			$depth = 0;
			
			$DSP->extra_css = $_SERVER['DOCUMENT_ROOT'].'/'.$PREFS->default_ini['system_folder'].'/modules/helmsman/css/styles.css';
			
			$DSP->body .= '<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/jquery-1.3.2.min.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/interface_1_2/interface.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/inestedsortable-1.0.1.pack.js"></script>
			<script type="text/javascript" src="'.$PREFS->core_ini['site_url'].$PREFS->default_ini['system_folder'].'/modules/helmsman/js/scripts.js"></script>'.
			'<script type="text/javascript">
				function returnNewItem(counter, the_level) {
					var item_string = "<li class=\'sortable-navitem\'><div class=\'hidden\'><input type=\'hidden\' name=\'data["+counter+"][link_depth]\' value=\'\' /></div><div class=\'example-link\'><a href=\'#\'>&nbsp;</a></div><a href=\'javascript:void(0);\' title=\'Move\' class=\'handlebar\'><img src=\'/console/modules/helmsman/img/draggable.gif\' /></a><a href=\'javascript:void(0);\' title=\'Delete\' class=\'delete-link\'><img src=\'/themes/cp_global_images/delete.png\' alt=\'Delete\' title=\'Delete\' /></a><input dir=\'ltr\'  style=\'width:200px\' type=\'text\' name=\'data["+counter+"][link_url]\' id=\'data"+counter+"link_url\' value=\'\' size=\'50\' maxlength=\'255\' class=\'input\' /><input  dir=\'ltr\'  style=\'width:200px\' type=\'text\' name=\'data["+counter+"][link_title]\' id=\'data"+counter+"link_title\' value=\'\' size=\'50\' maxlength=\'255\' class=\'leftmost input\' /><div class=\'clear-hack\'>&nbsp;</div></li>";
					return item_string;
				}
			</script>'.
				$DSP->qdiv('longWrapper',
					$DSP->qdiv('add-new-item', '<a href="javascript:void(0);"><span>Add Another Navigation Item</span></a>').
					$open_form.
					$this->output_navigation_items_forms($navigation_items, $counter, $depth)
				);
		}
		
		/**
		 * Takes incoming string and replaces html-encoded entities
		 *
		 * @access	public
		 * @param	string $text The text to make safe
		 * @return	string
		 */
		function incoming_entities($text)
		{
			return preg_replace("/&(?![#a-z0-9]+;)/i", "x%x%", $text);
		}
		
		/**
		 * checks to see if mb_encode_numericentity function is available and then uses it, if not uses htmlentities to encode the input string
		 *
		 * @access	public
		 * @param	string $text string to encode as entities
		 * @return	string
		 */
		function encode_entities($text)
		{
			return (function_exists('mb_encode_numericentity'))
			?	 $this->encode_high($text)
			:	 htmlentities($text, ENT_NOQUOTES, "utf-8");
		}
		
		/**
		 * Encodes a character as an HTML numeric string reference
		 *
		 * @access	public
		 * @param	string $text number that tells function the current depth of the navigation
		 * @param	string optional $charset tells the function if the current section needs is already set to open
		 * @return	string
		 */
		function encode_high($text, $charset = "UTF-8")
		{
			return mb_encode_numericentity($text, $this->cmap(), $charset);
		}
		
		/**
		 * Returns the array that specifies code area to convert.
		 *
		 * @access	public
		 * @return	array
		 */
		function cmap()
		{
			$f = 0xffff;
			$cmap = array(
				0x0080, 0xffff, 0, $f);
			return $cmap;
		}
		
		/**
		 * Grabs the first level of the navigation and kicks off the navigation array construction iterator
		 *
		 * @access	public
		 * @return	array
		 */
		function get_navigation_array()
		{
			global $DB;
			
			$result = $DB->query("SELECT parent_id FROM `exp_helmsman` WHERE parent_id<>0 GROUP BY parent_id");
			
			$top_level = $DB->query("SELECT * FROM `exp_helmsman` WHERE parent_id=0 ORDER BY sequence ASC");
			
			return $this->get_navigation_iterator($top_level->result);
		}
		
		/**
		 * Constructs the top level navigation into formatted array and then recursively loops through to get the sub-levels and construct them
		 *
		 * @access	public
		 * @param	array $sections that contains the current level's navigation items
		 * @return	array
		 */
		function get_navigation_iterator($results)
		{
			global $DB;
			
			$navigation = array();
			
			foreach($results as $nav_item)
			{
				$navigation[$nav_item['id']] = array(
					"title" => $nav_item['title'],
					"html_title" => $nav_item['html_title'],
					"url" => $nav_item['url'],
				);
				$children = $DB->query("SELECT * FROM `exp_helmsman` WHERE parent_id=".$nav_item['id']." ORDER BY sequence ASC");
				if(count($children->result)>0)
				{
					$navigation[$nav_item['id']]['children'] = $this->get_navigation_iterator($children->result);
				}
			}
			return $navigation;
		}
		
		/**
		 * Recursive function that outputs the final navigation form items and closes the form
		 *
		 * @access	public
		 * @param	array $sections that contains the current level's navigation items
		 * @param	int $counter the overall counter for navigation items
		 * @param	int $depth number that tells function the current depth of the navigation
		 * @return	string
		 */
		function output_navigation_items_forms($sections, &$counter, $depth)
		{
			global $DSP, $PREFS, $LANG;
			
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
			
			foreach($sections as $key => $section)
			{
				if(!mb_check_encoding($section['title'], 'utf-8'))
				{
					$section['title'] = mb_convert_encoding($section['title'], 'utf-8');
				}
				
				if($section_counter%2==1)
				{
					$extra_class = ' alt';
				} else {
					$extra_class = '';
				}
				
				$return .= '<li class="';
				
				if(!$this->top_level_lock || ($this->top_level_lock && $depth>0))
				{
					$return .= 'sortable-navitem';
				}
				
				$return .= $extra_class.'">'."\r\n".
				$DSP->input_hidden('data['.$counter.'][link_depth]', $depth)."\r\n".
				'<div class="example-link"><a href="'.substr($PREFS->core_ini['site_url'], 0, -1).$section['url'].'">'.$section['html_title'].'</a></div>';
				if(!$this->top_level_lock || ($this->top_level_lock && $depth>0)) {
					$return .= $DSP->anchor('javascript:void(0);', '<img src="/console/modules/helmsman/img/draggable.gif" />', 'title="Move" class="handlebar"').
					$DSP->anchor('javascript:void(0);', '<img src="/themes/cp_global_images/delete.png" alt="Delete" title="Delete" />', 'title="Delete" class="delete-link"');
				} else {
					$return .= $DSP->anchor('javascript:void(0);', '<img src="/console/modules/helmsman/img/lock.gif" />', 'title="Move" class="lock"');
				}
				$return .= $DSP->input_text('data['.$counter.'][link_url]', $section['url'], '50', '255', 'input', '200px', $this->input_extras($depth)).
					$DSP->input_text('data['.$counter.'][link_title]', $section['title'], '50', '255', 'leftmost input', '200px', $this->input_extras($depth)).
					'<div class="clear-hack">&nbsp;</div>';
				$counter++;
				if(isset($section['children']) && count($section['children'])>0) {
					$pass_depth = $depth+1;
					$return .= $this->output_navigation_items_forms($section['children'], $counter, $pass_depth)."\r\n";
				}
				$return .= '</li>'."\r\n";
				$section_counter++;
			}
			
			if($depth==0)
			{
				$return .= '</ol>'.
					'<div id="serialized"><textarea style="display:none;" name="serialized_data" id="serialized_data">&nbsp;</textarea></div>'.
					$DSP->input_submit($LANG->line('helmsman_save')).
					$DSP->form_close().
					'<div id="current-count" style="display:none;">'.$counter.'</div>';
			} else {
				$return .= '</ol>'."\r\n";
			}
			
			return $return;
		}
		
		/**
		 * Function that currently sets only one extra option, but could handle others. Currently sets the read-only status on inputs if $this->top_level_lock is on
		 *
		 * @access	public
		 * @param	int $depth number that tells function the current depth of the navigation
		 * @return	string
		 */
		function input_extras($depth) {
			$return = '';
			if($this->top_level_lock && $depth==0)
			{
				$return .= 'readonly';
			}
			return $return;
		}
		
		/**
		 * Removes all the navigation so that the "new" nav can be built up from scratch and then kicks off the save iterator
		 *
		 * @access	public
		 * @param	array $navigation_items takes the posted form data
		 * @return	boolean
		 */
		function navigation_save($navigation_items)
		{
			global $DB, $PREFS;
			
			// if($this->top_level_lock) {
			// 	echo 'DELETE FROM `exp_helmsman` WHERE `parent_id`<>0';
			// 	$delete_all = $DB->query('DELETE FROM `exp_helmsman` WHERE `parent_id`<>0');
			// } else {
				// echo 'DELETE FROM `exp_helmsman`';
				$delete_all = $DB->query('DELETE FROM `exp_helmsman`');
			// }
			
			$this->save_iterator($navigation_items);
			
			//exit();
			
			return true;
		}
		
		/**
		 * Loops through the form data and saves the items
		 *
		 * @access	public
		 * @param	array $the_data the posted array of form elements
		 * @param	int $current_depth number that tells function the current depth of the navigation
		 * @return	boolean
		 */
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
						
						$value['slug'] = $this->create_slug($value['link_title']);
						
						// if(!$this->top_level_lock || ($this->top_level_lock && isset($current_parent) && $current_parent>0)) {
							if(isset($current_parent)) {
								// echo 'INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", '.$current_parent.', '.$counter.')';
								$DB->query('INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", '.$current_parent.', '.$counter.')');
							} else {
								// echo 'INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", 0, '.$counter.')';
								$DB->query('INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", 0, '.$counter.')');
							}
						// }
						
						$the_data[$key] = $DB->insert_id;
					}
					$counter++;
				}
			}
			
			$this->array_unsetter($the_data);
			
			return true;
		}
		
		/**
		 * Unsets navigation items without sub-navigation and renumbers the indices of the navigation array
		 *
		 * @access	public
		 * @param	int $depth number that tells function the current depth of the navigation
		 * @return	string
		 */
		function array_unsetter($the_data) {
			if(!empty($the_data))
			{
				$the_data = array_merge($the_data);
				foreach($the_data as $key => $value) {
					if(count($the_data[$key])==1 && isset($the_data[$key+1]) && count($the_data[$key+1])==1)
					{
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
		
		/**
		 * Creates a slug from a string
		 *
		 * @access	public
		 * @param	string $string the string that needs to be turned into a slug
		 * @return	string
		 */
		function create_slug($string)
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
		
		/**
		 * Installs the Helmsman module - adds it to various parts of the DB and creates the Helmsman-specific table
		 *
		 * @access	public
		 * @return	boolean
		 */
		function helmsman_module_install()
		{
			global $DB;
			
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

			$sql[] = "INSERT INTO exp_modules (module_id,
												module_name,
												module_version,
												has_cp_backend)
												VALUES
												(null,
												'Helmsman',
												'$this->version',
												'y')";
			
			foreach($sql as $query)
			{
				$DB->query($query);
			}
			return true;
		}

		/**
		 * Un-installs the Helmsman module (removes it from the various parts of the database)
		 *
		 * @access	public
		 * @return	boolean
		 */
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

	/* End of file mcp.helmsman.php */
	/* Location: ./system/modules/helmsman/mcp.helmsman.php */