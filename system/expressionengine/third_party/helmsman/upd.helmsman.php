<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

class Helmsman_upd {

	var $version = '2.0';

	function Helmsman_upd()
	{
		// $this->__construct();
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->load->dbforge();
	}
	
	// function __construct() {
	// 	// Make a local reference to the ExpressionEngine super object
	// 	$this->EE =& get_instance();
	// 	$this->EE->load->dbforge();
	// }
	
	function install()
	{
		$fields = array(
							'helmsman_id'=>	array('type' => 'int',
											'constraint'	=>	'6',
											'unsigned'	=>	TRUE,
											'auto_increment'=>	TRUE),
							'title'	=>	array('type' => 'varchar',
										'constraint' => '255'),
							'html_title'	=>	array('type' => 'varchar',
										'constraint' => '255'),
							'slug'	=>	array('type' => 'varchar',
										'constraint' => '255'),
							'url'	=>	array('type' => 'varchar',
										'constraint' => '255'),
							'parent_id'=>	array('type' => 'int',
											'constraint'	=>	'6',
											'unsigned'	=>	TRUE,
											'default'	=>	'0'),
							'sequence'=>	array('type' => 'int',
											'constraint'	=>	'6',
											'unsigned'	=>	TRUE),
						);
		
		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('helmsman_id', TRUE);
		$this->EE->dbforge->add_key('parent_id');
		$this->EE->dbforge->create_table('helmsman');
		
		$data = array(
			'module_name' => 'Helmsman' ,
			'module_version' => $this->version,
			'has_cp_backend' => 'y'
		);
		
		$this->EE->db->insert('modules', $data);
		
		return TRUE;
	}

	function uninstall()
	{
		$this->EE->load->dbforge();

		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Helmsman'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Helmsman');
		$this->EE->db->delete('modules');

		$this->EE->dbforge->drop_table('helmsman');

		return TRUE;
	}

	function update($current='')
	{
		if ($current < 2.0)
		{
			// Do your 2.0 version update queries
		}
		if ($current < 3.0)
		{
			// Do your 3.0 v. update queries
		}

		return TRUE;
	}
}
/* END Class */

/* End of file upd.package_name.php */
/* Location: ./system/expressionengine/third_party/upd.package_name.php */