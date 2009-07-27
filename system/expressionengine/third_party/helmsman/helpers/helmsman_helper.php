<?php if(! defined('BASEPATH')) exit('No direct script access allowed');

if(! function_exists('output_navigation_items_forms'))
{
	/**
	 * Recursive function that outputs the final navigation form items and closes the form
	 *
	 * @access	public
	 * @param	array $sections that contains the current level's navigation items
	 * @param	int $counter the overall counter for navigation items
	 * @param	int $depth number that tells function the current depth of the navigation
	 * @return	string
	 */
	function output_navigation_items_forms($sections, &$counter, $depth, $top_level_lock=false)
	{
		$EE =& get_instance();
		
		if($depth==0) {
			$return = $EE->dsp->qdiv('top-labels', $EE->dsp->qdiv('title-label', 'Title').$EE->dsp->qdiv('link-label', 'Link')).
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
		
			if(!$top_level_lock || ($top_level_lock && $depth>0))
			{
				$return .= 'sortable-navitem';
			}
		
			$return .= $extra_class.'">'."\r\n".
			$EE->dsp->input_hidden('data['.$counter.'][link_depth]', $depth)."\r\n".
			'<div class="example-link"><a href="'.substr($EE->config->item('site_url'), 0, -1).$section['url'].'">'.$section['html_title'].'</a></div>';
			if(!$top_level_lock || ($top_level_lock && $depth>0)) {
				$return .= $EE->dsp->anchor('javascript:void(0);', '<img src="/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/images/draggable.gif" />', 'title="Move" class="handlebar"').
				$EE->dsp->anchor('javascript:void(0);', '<img src="/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/images/delete.png" alt="Delete" title="Delete" />', 'title="Delete" class="delete-link"');
			} else {
				$return .= $EE->dsp->anchor('javascript:void(0);', '<img src="/'.SYSDIR.'/'.basename(APPPATH).'/third_party/helmsman/images/lock.gif" />', 'title="Move" class="lock"');
			}
			$return .= $EE->dsp->input_text('data['.$counter.'][link_url]', $section['url'], '50', '255', 'input', '200px', input_extras($depth, $top_level_lock)).
				$EE->dsp->input_text('data['.$counter.'][link_title]', $section['title'], '50', '255', 'leftmost input', '200px', input_extras($depth, $top_level_lock)).
				'<div class="clear-hack">&nbsp;</div>';
			$counter++;
			if(isset($section['children']) && count($section['children'])>0) {
				$pass_depth = $depth+1;
				$return .= output_navigation_items_forms($section['children'], $counter, $pass_depth)."\r\n";
			}
			$return .= '</li>'."\r\n";
			$section_counter++;
		}
	
		if($depth==0)
		{
			$return .= '</ol>'.
				'<div id="serialized"><textarea style="display:none;" name="serialized_data" id="serialized_data">&nbsp;</textarea></div>'.
				$EE->dsp->input_submit($EE->lang->line('helmsman_save')).
				$EE->dsp->form_close().
				'<div id="current-count" style="display:none;">'.$counter.'</div>';
		} else {
			$return .= '</ol>'."\r\n";
		}
	
		return $return;
	}
}

if(! function_exists('input_extras')) {
	/**
	 * Function that currently sets only one extra option, but could handle others. Currently sets the read-only status on inputs if $top_level_lock is on
	 *
	 * @access	public
	 * @param	int $depth number that tells function the current depth of the navigation
	 * @return	string
	 */
	function input_extras($depth, $top_level_lock) {
		$return = '';
		if($top_level_lock && $depth==0)
		{
			$return .= 'readonly';
		}
		return $return;
	}
}

?>