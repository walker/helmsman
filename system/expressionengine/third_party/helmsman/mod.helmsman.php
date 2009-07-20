<?php if(!defined('BASEPATH')) exit('Invalid file request');
	
	class Helmsman {
		
		/**
		 * Constructor class that makes sure Helmsman is set up and ready
		 *
		 * @access	public
		 * @return	string
		 */
		function Helmsman()
		{
			$this->EE =& get_instance();
			/* Construct Items */
			$this->collapse_level = ($this->EE->tmpl->fetch_param('collapse_level')!==false) ? $this->EE->tmpl->fetch_param('collapse_level') : null;
			$this->separate_menus = ($this->EE->tmpl->fetch_param('separate')!==false) ? true : false;
			$this->sub_menu = ($this->EE->tmpl->fetch_param('is_sub')!==false) ? true : false;
			settype($this->collapse_level, "integer");
			
			$this->void = ($this->EE->tmpl->fetch_param('void')) ? true : false;
			$this->currently_open = ($this->EE->tmpl->fetch_param('currently_open')) ? $this->EE->tmpl->fetch_param('currently_open') : null;
			$this->display_type = ($this->EE->tmpl->fetch_param('display_type')=='ol') ? 'ol' : 'ul';
			$this->prefix = ($this->EE->tmpl->fetch_param('prefix')) ? 'helm-' : '';
			$this->current = ($this->EE->tmpl->fetch_param('current')) ? $this->EE->tmpl->fetch_param('current') : $_SERVER['REQUEST_URI'];
			
			$this->nav_array = $this->get_navigation_array();
			
			/* Kick off "Construct Menu" Functionality */
			$this->return_data = $this->construct_menu();
			
			/* Return The Menu */
			return $this->return_data;
		}

		/**
		 * Constructor class that makes sure Helmsman is set up and ready
		 *
		 * @access	public
		 * @return	string
		 */
		function construct_menu() {
			$counter = 0;
			$depth = 0;

			if(!$this->sub_menu) {
				return $this->items($this->nav_array, $counter, $depth);
			} else {
				return $this->construct_sub_menu($this->nav_array, $counter, $depth);
			}
		}

		/**
		 * Recursive function that outputs the final navigation list
		 *
		 * @access	public
		 * @param	array $sections that contains the current level's navigation items
		 * @param	int $counter the overall counter for navigation items
		 * @param	int $depth number that tells function the current depth of the navigation
		 * @param	boolean optional $currently_open tells the function if the current section needs is already set to open
		 * @return	string
		 */
		function items($sections, &$counter, $depth, $currently_open=false) {
			if($depth==0) {
				$return = '<'.$this->display_type.' id="'.$this->prefix.'navMain">'."\r\n";
			} else {
				$return = '<'.$this->display_type.' class="'.$this->prefix.'navSub';
				if($this->collapse_level!==null) {
					if(!$currently_open) {
						$currently_open = $this->contains_currently_open($sections);
					}

					if($depth>=$this->collapse_level && !$currently_open) {
						$return .= ' '.$this->prefix.'closed-section';
					} else if($currently_open && $depth!=1) {
						$return .= ' '.$this->prefix.'open-section';
					}
				}
				$return .= '">'."\r\n";
			}

			$section_counter = 1;
			$section_total = count($sections);

			foreach($sections as $key => $section) {
				$extra_class = '';
				if($counter%2==1) $extra_class .= ' '.$this->prefix.'main-alt';
				if($section_counter%2==1) $extra_class .= ' '.$this->prefix.'level-alt';

				$return .= "\r\n".'<li class="';

				if($section_counter==1) { $return .= 'first '; } else if($section_counter==$section_total) { $return .= 'last '; }

				$test_items = explode('/', rtrim($section['slug'], '/'));
				if(rtrim($this->current, '/')==rtrim($section['url'], '/') || $this->current==$section['slug'] || (isset($this->EE->uri->segments[1]) && $this->EE->uri->segments[1]==$test_items[0]) || $section['slug']==$this->currently_open) $return .= 'current ';

				if(isset($section['children']) && count($section['children'])>0 && $this->void) {
					$link = 'javascript:void(0);';
				} else {
					if(!strpos($section['url'], 'http://')) {
						$link = substr($this->EE->config->item('site_url'), 0, -1).$section['url'];
					} else {
						$link = $section['url'];
					}
				}

				$return .= $this->prefix.'navitem'.$extra_class.'"';
				if($depth==0) {
 					$return .= ' id="'.$this->prefix.$section['slug'].'"';
				}
				$return .= '>
						<a href="'.$link.'"><span>'.$section['html_title'].'</span></a>';
				if($section_counter < $section_total && $depth>0)$return.='|';

				$return .= "\r\n";

				$counter++;
				if(isset($section['children']) && count($section['children'])>0 && !$this->separate_menus) {
					$pass_depth = $depth+1;
					$return .= $this->items($section['children'], $counter, $pass_depth, $currently_open)."\r\n";
				}
				$return .= '</li>'."\r\n";
				$section_counter++;
			}

			$return .= '</'.$this->display_type.'>'."\r\n";

			return $return;
		}

		function construct_sub_menu($sections, &$counter, $depth, $currently_open=false) {
			$return = '';
			foreach($sections as $key => $section) {
				$test_items = explode('/', trim($section['url'], '/'));

				if(rtrim($this->current, '/')==rtrim($section['url'], '/') || $this->current==$section['slug'] || (isset($this->EE->uri->segments[1]) && $this->EE->uri->segments[1]==$test_items[0]) || $this->currently_open==$section['slug']) {
					if(isset($section['children']) && count($section['children'])>0) {
						$pass_depth = $depth+1;
						$return .= $this->items($section['children'], $counter, $pass_depth, $currently_open)."\r\n";
					}
				}
			}
			return $return;
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

			$top_level = $EE->db->query("SELECT * FROM `exp_helmsman` WHERE parent_id=0 ORDER BY sequence ASC");

			return $this->get_navigation_iterator($top_level->result);
		}

		/**
		 * Constructs the top level navigation into formatted array and then recursively loops through to get the sub-levels and construct them
		 *
		 * @access	public
		 * @param	array $sections that contains the current level's navigation items
		 * @return	array
		 */
		function get_navigation_iterator($results) {
			$navigation = array();

			foreach($results as $nav_item) {
				$navigation[$nav_item['id']] = array(
					"title" => $nav_item['title'],
					"html_title" => $nav_item['html_title'],
					"url" => $nav_item['url'],
					"slug" => $nav_item['slug'],
				);
				$children = $EE->db->query("SELECT * FROM `exp_helmsman` WHERE parent_id=".$nav_item['id']." ORDER BY sequence ASC");
				if(count($children->result)>0) {
					$navigation[$nav_item['id']]['children'] = $this->get_navigation_iterator($children->result);
				}
			}

			return $navigation;
		}

		/**
		 * Figures out if the current section contains the currently open item (do not use on top level of nav)
		 *
		 * @access	public
		 * @param	array $sections the navigation array for a section of the navigation
		 * @return	boolean
		 */
		function contains_currently_open($sections) {
			$currently_open = false;
			foreach($sections as $section) {
				if(rtrim($this->current, '/')==rtrim($section['url'], '/') || $this->currently_open==$section['slug'])
				{
					$currently_open = true;
					break;
				}
				if(!$currently_open) {
					if(isset($section['children']) && !empty($section['children'])) {
						$currently_open = $this->contains_currently_open($section['children']);
					}
				}
			}

			return $currently_open;
		}
	}

	/* End of file mod.helmsman.php */
	/* Location: ./system/modules/helmsman/mod.helmsman.php */