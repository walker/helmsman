<?php

	class Helmsman_CP {
		var $version = '0.1';
		
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
					case 'modify':
						$this->modify_nav_item();
						break;
					case 'update':
						$this->update_nav_item();
						break;
					default:
						$this->helmsman_home();
						break;
				}
			}
		}
		
		function helmsman_home()
		{
			global $DSP, $LANG;
			
			$DSP->title = $LANG->line('helmsman_module_name');
			$DSP->crumb = $DSP->anchor(BASE.
										AMP.'C=modules'.
										AMP.'M=helmsman',
			$LANG->line('helmsman_module_name'));
			$DSP->crumb .= $DSP->crumb_item($LANG->line('helmsman_menu'));
			
			$DSP->body .= $DSP->heading($LANG->line('helmsman_menu'));
			
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->heading($DSP->anchor(BASE.
																				AMP.'C=modules'.
																				AMP.'M=helmsman'.
																				AMP.'P=add',
																				$LANG->line('add_nav_item')),
																				5));
			
			$DSP->body .= $DSP->qdiv('itemWrapper', $DSP->heading($DSP->anchor(BASE.
																				AMP.'C=modules'.
																				AMP.'M=helmsman'.
																				AMP.'P=modify',
																				$LANG->line('modify_nav_item')),
																				5));
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
												`fortune_id` INT(6) UNSIGNED NOT NULL AUTO_INCREMENT,
												`fortune_text` TEXT NOT NULL ,
												PRIMARY KEY (`fortune_id`));";

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