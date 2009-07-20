<?php

	class Helmsman_mcp
	{
		//Version of Helmsman Control Panel
		var $version = '2.0';
		
		//change to true to not allow the user to modify the top level of the menu
		var $top_level_lock = false;
		
		/**
		 * Constructor function that kicks off the main Helmsman control panel page
		 *
		 * @access	public
		 */
		function Helmsman_mcp()
		{
			$this->EE =& get_instance();
		}
		
		/**
		 * Constructs the Helmsman control panel by modifying the $this->EE->DSP's title, crumb, and body variables with the data retrieved from the other Helmsman functions
		 *
		 * @access	public
		 */
		function index()
		{
			$vars = array();
			
			if(isset($_POST['data']) && !empty($_POST['data']))
			{
				$the_data = $_POST['data'];
				$the_data = array_merge($the_data);
				
				//save it
				$this->navigation_save($the_data);
			}
			
			$this->EE->dsp->extra_css = BASE.'/helmsman/css/styles.css';
			$this->EE->jquery->plugin('/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/javascript/interface.js', TRUE);
			$this->EE->jquery->plugin('/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/javascript/inestedsortable.js', TRUE);
			$this->EE->javascript->output(
					'jQuery(document).ready(function() {
						jQuery(\'.add-new-item a\').click(function() {
							var num = jQuery(\'#current-count\').html();
							num = parseInt(num);
							var num_inc = num+1;
							num_inc = num_inc.toString();

							if(jQuery(\'#master-list\').children(\'li\').children(\'input\').attr(\'readonly\')==true) {
								var the_level = jQuery(\'.sub-list:last\').children(\'li:last\').children(\'div.hidden\').children(\'input\').attr(\'value\');
								if(jQuery(this).parent().attr(\'id\')==\'add-new-item-top\') {
									jQuery(\'.sub-list:first\').append(returnNewItem(num, the_level));
								} else {
									jQuery(\'.sub-list:last\').append(returnNewItem(num, the_level));
								}
							} else {
								if(jQuery(this).parent().attr(\'id\')==\'add-new-item-top\') {
									jQuery(\'#master-list\').prepend(returnNewItem(num, 0));
								} else {
									jQuery(\'#master-list\').append(returnNewItem(num, 0));
								}
							}
							jQuery(\'#current-count\').html(num_inc);
							bindInputKeypresses();
							bindDelete();
							bindDeleteHover();
							bindSortable();
						});
						bindInputKeypresses();
						bindDelete();
						bindDeleteHover();
						bindSortable();
					});

					function bindInputKeypresses() {
						jQuery(\'.input\').keyup(function() {
							if(jQuery(this).attr(\'class\')==\'leftmost input\') {
								var link = \'<a href="\'+jQuery(this).prev().attr(\'value\')+\'">\'+jQuery(this).attr(\'value\')+\'<\'+\'/\'+\'a>\';
							} else {
								var link = \'<a href="\'+jQuery(this).attr(\'value\')+\'">\'+jQuery(this).next().attr(\'value\')+\'<\'+\'/\'+\'a>\';
							}
							jQuery(this).parent().children(\'.example-link\').html(link);
						});
					}

					function bindSortable() {
						if(jQuery(\'#master-list\').children(\'li\').children(\'input\').attr(\'readonly\')==true) {
							jQuery.each(jQuery(\'#master-list ol\'), function() {
								jQuery(\'#\'+jQuery(this).attr(\'id\')).NestedSortable({
									accept: \'sortable-navitem\',
									handle: \'.handlebar\',
									nestingPxSpace: 20,
									helperclass: \'dropzone\',
									opacity: 0.6,
									onChange: function(serialized) {
										jQuery.each(jQuery(\'#master-list li\'), function() {
											var anid = jQuery(this).parent()[0].id;
											var counter = 0;
											while(anid!=\'master-list\') {
												counter++;
												num_parents = \'.parent()\';
												for(i=1;i<counter;i++) { num_parents += \'.parent()\'; }
												the_next = "jQuery(this)"+num_parents+"[0].id";
												// console.log(the_next);
												anid = eval(the_next);
												var depth = ((i-1)/2)+1;
											}
											if(typeof(depth)==\'undefined\')
											{
												var depth = 1;
											}
											// console.log(depth);
											jQuery(this).children(\'div.hidden\').children(\'input\').attr(\'value\', depth);
										});
									}
								});
							});
						} else {
							jQuery(\'#master-list\').NestedSortable({
								accept: \'sortable-navitem\',
								handle: \'.handlebar\',
								nestingPxSpace: 20,
								helperclass: \'dropzone\',
								opacity: 0.6,
								onChange: function(serialized) {
									jQuery.each(jQuery(\'#master-list li\'), function() {
										var anid = jQuery(this).parent()[0].id;
										var counter = 0;
										while(anid!=\'master-list\') {
											counter++;
											num_parents = \'.parent()\';
											for(i=1;i<counter;i++) { num_parents += \'.parent()\'; }
											the_next = "jQuery(this)"+num_parents+"[0].id";
											// console.log(the_next);
											anid = eval(the_next);
											var depth = ((i-1)/2)+1;
										}
										if(typeof(depth)==\'undefined\')
										{
											var depth = 1;
										}
										// console.log(depth);
										jQuery(this).children(\'div.hidden\').children(\'input\').attr(\'value\', depth);
									});
								}
							});
						}
					}
						
					function bindDeleteHover() {
						jQuery(\'a.delete-link\').hover(function() {
							// console.log(jQuery(this).parent().html());
							jQuery(this).parent().css(\'background-color\', \'pink\');
						}, function() {
							jQuery(this).parent().css(\'background-color\', \'\');
						});
					}
					
					function bindDelete() {
						jQuery(\'a.delete-link\').click(function() {
							jQuery(this).parent().remove();
							// console.log(jQuery(this).parent().html());
						});
					}
					
					function returnNewItem(counter, the_level) {
						var item_string = "<li class=\'sortable-navitem\'><div class=\'hidden\'><input type=\'hidden\' name=\'data["+counter+"][link_depth]\' value=\'\' /><"+"/"+"div><div class=\'example-link\'><a href=\'#\'>&nbsp;<"+"/"+"a><"+"/"+"div><a href=\'javascript:void(0);\' title=\'Move\' class=\'handlebar\'><img src=\'/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/images/draggable.gif\' /><"+"/"+"a><a href=\'javascript:void(0);\' title=\'Delete\' class=\'delete-link\'><img src=\'/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/images/delete.png\' alt=\'Delete\' title=\'Delete\' /><"+"/"+"a><input dir=\'ltr\'  style=\'width:200px\' type=\'text\' name=\'data["+counter+"][link_url]\' id=\'data"+counter+"link_url\' value=\'\' size=\'50\' maxlength=\'255\' class=\'input\' /><input  dir=\'ltr\'  style=\'width:200px\' type=\'text\' name=\'data["+counter+"][link_title]\' id=\'data"+counter+"link_title\' value=\'\' size=\'50\' maxlength=\'255\' class=\'leftmost input\' /><div class=\'clear-hack\'>&nbsp;<"+"/"+"div><"+"/"+"li>";
						return item_string;
					}'
			);
			
			$this->EE->javascript->compile();
			
			$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('helmsman_module_name'));
			$vars['form_action'] = 'D=cp'.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=helmsman';
			$vars['form_attributes'] = array('method' => 'post', 'name' => 'nav_form', 'id' => 'nav_form');
			
			$this->EE->load->library('javascript');
			$this->EE->load->helper('form');
			$this->EE->load->helper('helmsman');
			
			$vars['top_level_lock'] = $this->top_level_lock;
			$vars['counter'] = 0;
			$vars['depth'] = 0;
			
			$vars['navigation_items'] = $this->get_navigation_array();
			
			return $this->EE->load->view('index', $vars, TRUE);
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
			$result = $this->EE->db->query("SELECT parent_id FROM `exp_helmsman` WHERE parent_id<>0 GROUP BY parent_id");
			
			$top_level = $this->EE->db->query("SELECT * FROM `exp_helmsman` WHERE parent_id=0 ORDER BY sequence ASC");
			
			return $this->get_navigation_iterator($top_level->result_array());
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
			$navigation = array();
			
			foreach($results as $nav_item)
			{
				$navigation[$nav_item['helmsman_id']] = array(
					"title" => $nav_item['title'],
					"html_title" => $nav_item['html_title'],
					"url" => $nav_item['url'],
				);
				$children = $this->EE->db->query("SELECT * FROM `exp_helmsman` WHERE parent_id=".$nav_item['helmsman_id']." ORDER BY sequence ASC");
				if(count($children->result_array())>0)
				{
					$navigation[$nav_item['helmsman_id']]['children'] = $this->get_navigation_iterator($children->result_array());
				}
			}
			return $navigation;
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
			// if($this->top_level_lock) {
			// 	echo 'DELETE FROM `exp_helmsman` WHERE `parent_id`<>0';
			// 	$delete_all = $this->EE->db->query('DELETE FROM `exp_helmsman` WHERE `parent_id`<>0');
			// } else {
				// echo 'DELETE FROM `exp_helmsman`';
				$delete_all = $this->EE->db->query('DELETE FROM `exp_helmsman`');
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
						
						// Set the values to be the column names from the DB (just for "referencability")
						$value['title'] = $value['link_title'];
						$value['url'] = $value['link_url'];
						
						$value['slug'] = $this->create_slug($value['link_title']);
						
						if(!empty($value['link_title']) && !empty($value['link_url'])) {
							// if(!$this->top_level_lock || ($this->top_level_lock && isset($current_parent) && $current_parent>0)) {
								if(isset($current_parent)) {
									// echo 'INSERT INTO `exp_helmsman` (helmsman_id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", '.$current_parent.', '.$counter.')';
									$this->EE->db->query('INSERT INTO `exp_helmsman` (helmsman_id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", '.$current_parent.', '.$counter.')');
								} else {
									// echo 'INSERT INTO `exp_helmsman` (id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", 0, '.$counter.')';
									$this->EE->db->query('INSERT INTO `exp_helmsman` (helmsman_id, title, html_title, slug, url, parent_id, sequence) VALUES (null, "'.addslashes($value['title']).'", "'.addslashes($value['html_title']).'", "'.addslashes($value['slug']).'", "'.addslashes($value['url']).'", 0, '.$counter.')');
								}
							// }
						}
						
						$the_data[$key] = $this->EE->db->insert_id();
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
				if(!empty($the_data)) {
					$this->save_iterator($the_data);
				}
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
	}

	/* End of file mcp.helmsman.php */
	/* Location: ./system/modules/helmsman/mcp.helmsman.php */