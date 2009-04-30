Installation:
	1. Place the language/english/lang.helmsman.php file in /<system folder>/language/english/
	2. Place the modules/helmsman folder  from this package into /<system folder>/modules/

Back-end Management
	
	1. Click on the "Modules" tab.
	2. Click "Install" in the "Action" column in the "Helmsman" row.
	
	When adding menu items, if they are under the same URL as the expression engine install, start the url with "/".
	
	Example:
		
		This url: http://domain.com/expertise/employee_relations
		Would be entered into Helmsman in this manner: /expertise/employee_relations

Front-end Use
	
	Place this tag where you need the menu:
		
		{exp:helmsman}
		
	Attributes
		
		collapse_level: can be set to 0 or 1. If not specified, defaults to "none". This determines which level of menu items will get a "closed" class assigned to them for CSS hooks.
		
		currently_open: specify the id attribute of an entry on this attribute to cause that specific menu item to the "open" or "active" menu item. If not specified, this uses the current request url to automatically open the proper item. Helmsman does the automatic "open" by matching the url from the field in the back-end to the currently open url, this is why setting the url as noted above in "Back-end" management is so important.
		
		display_type: can be set to "ol" or "ul". Defaults to "ul"
		
		prefix: specify with a value of "yes" or "true" and the "helm-" prefix will be attached to all id & class attributes output to the menu's structure. (This allows Helmsman to avoid colliding with other IDs and Classes in the html of your page).
		
		void: If set, this causes any navigation element with child elements to have it's "href" attribute set to have no action. These item will then only work as part of the accordion structure, they will not link anywhere, even if a link is specified for them in the back-end.
		
		separate: can be set to 0 or 1, defaults to 0 (not deparate) outputs everything as 1 menu. if set please use other tag {exp:helmsman is_sub="true"} to output the submenu on sub-pages.
		
		is_sub: can be set to "true" do not put in helmsman tag if this is not the sub menu you want output.
	

	Once you have configured the top-level of the menu you are going to be managing using this module, you can edit the mcp.helmsman.php file and turn off the ability to edit that level (change false to true on line 9 of mcp.helmsman.php). This switch is within the code so that site developer & designers do not have to worry about a client or end-user of the admin console messing with the top level of the menu.