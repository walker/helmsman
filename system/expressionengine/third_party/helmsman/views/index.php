<style type="text/css">
.longWrapper {
	width: 900px;
	margin: 0 auto;
	list-style-type: none;
}

.longWrapper ul li {
	width: 100%;
}

.longWrapper li {
	list-style-type: none;
}

.longWrapper table {
	border-left: 0;
	border-right: 0;
}

.longwrapper ol li.add {
	text-indent: -9999px;
}

ol#master-list {
	margin: 0;
	padding: 0;
	border-left: 1px solid #CAD0D5;
	border-right: 1px solid #CAD0D5;
}

ol#master-list ol {
	margin-top: 5px;
	padding-bottom: 5px;
	border-top: 1px solid #CAD0D5;
}

ol#master-list li {
	list-style: none;
	padding: 8px 0 17px 8px;
	background-color: #fff;
	border-bottom: 1px solid #CAD0D5;
}

ol#master-list li ol li {
	padding: 8px 0 17px 8px;
	background-color: #fff;
}

ol#master-list li.alt {
	background-color: #eef4f9;
}

ol#master-list li ol li {
	background-color: #fff;
	border-bottom: 1px solid #dde1e5;
}

ol#master-list li ol li.sectionalt {
	background-color: #eef4f9;
}

div.example-link {
	width: 150px;
	float: left;
}

li input.input {
	float: right;
	margin-right: 112px;
	position: relative;
	top: 3px;
}

li input.leftmost.input {
	margin-right: 10px;
}

a.handlebar img {
	float: right;
	border: 0;
	position: relative;
	top: 3px;
	padding-left: 20px;
	padding-right: 8px;
}

a.lock img {
	float: right;
	border: 0;
	position: relative;
	padding-right: 5px;
}

a.delete-link img {
	float: right;
	border: 0;
	position: relative;
	top: 3px;
}

/*#add-new-item {
	width: 100%;
	margin: 0 auto;
}*/

#nav_form .top-labels {
	background: #768e9d;
	padding: 7px 0;
}

#nav_form .title-label, #nav_form .link-label {
	color: #fff;
	font-weight: bold;
}

#nav_form .top-labels .title-label {
	margin-left: 420px;
	width: 200px;
	float: left;
}

#nav_form .top-labels .link-label {
	width: 200px;
	margin-left: 630px;
}

#nav_form .submit {
	margin: 15px 0 0 10px;
	width: 100px;
}

.dropzone {
	border-top: 3px solid #CAD0D5;
}
</style>
<div class="button add-new-item"><a href="javascript:void(0);">Add Another Navigation Item</a></div>

<div class="clear_left">&nbsp;</div>

<?php echo form_open($form_action, $form_attributes); ?>
	
	<?php echo output_navigation_items_forms($navigation_items, $counter, $depth, $top_level_lock); ?>