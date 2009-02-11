jQuery.noConflict();

jQuery(document).ready(function() {
	jQuery('.add-new-item a').click(function() {
		var num = jQuery('#current-count').html();
		num = parseInt(num);
		var num_inc = num+1;
		num_inc = num_inc.toString();
		jQuery('#master-list').append(returnNewItem(num));
		jQuery('#current-count').html(num_inc);
		bindDelete();
		bindDeleteHover();
		bindSortable();
	});
	
	jQuery('.input').keyup(function() {
		if(jQuery(this).attr('class')=='leftmost input') {
			var link = '<a href="'+jQuery(this).next().attr('value')+'">'+jQuery(this).attr('value')+'</a>';
		} else {
			var link = '<a href="'+jQuery(this).attr('value')+'">'+jQuery(this).prev().attr('value')+'</a>';
		}
		jQuery(this).parent().children('.example-link').html(link);
	});
	
	bindDelete();
	bindDeleteHover();
	bindSortable();
	resetMargin();
});

function bindSortable() {
	jQuery('#master-list').NestedSortable({
		accept: 'sortable-navitem',
		handle: '.handlebar',
		nestingPxSpace: 20,
		onChange: function(serialized) {
			jQuery.each(jQuery('#master-list li'), function() {
				var anid = jQuery(this).parent()[0].id;
				var counter = 0;
				while(anid!='master-list') {
					counter++;
					num_parents = '.parent()';
					for(i=1;i<counter;i++) { num_parents += '.parent()'; }
					the_next = "jQuery(this)"+num_parents+"[0].id";
					// console.log(the_next);
					anid = eval(the_next);
					var depth = ((i-1)/2)+1;
				}
				if(typeof(depth)=='undefined')
				{
					var depth = 1;
				}
				// console.log(depth);
				jQuery(this).children('div.hidden').children('input').attr('value', depth);
				amt = (120-(depth*20))
				amt = amt.toString();
				marg = amt+'px';
				jQuery(this).children('input.leftmost').css('margin-left', marg);
				//$("#" + this).text("My id is " + this + ".");
			});
		}
	});
}

function bindDeleteHover() {
	jQuery('a.delete-link').hover(function() {
		// console.log(jQuery(this).parent().html());
		jQuery(this).parent().css('background-color', 'pink');
	}, function() {
		jQuery(this).parent().css('background-color', 'transparent');
	});
}

function bindDelete() {
	jQuery('a.delete-link').click(function() {
		jQuery(this).parent().remove();
		// console.log(jQuery(this).parent().html());
	});
}

function resetMargin() {
	jQuery.each(jQuery('#master-list li'), function() {
		var anid = jQuery(this).parent()[0].id;
		var counter = 0;
		while(anid!='master-list') {
			counter++;
			num_parents = '.parent()';
			for(i=1;i<counter;i++) { num_parents += '.parent()'; }
			the_next = "jQuery(this)"+num_parents+"[0].id";
			// console.log(the_next);
			anid = eval(the_next);
			var depth = ((i-1)/2)+1;
		}
		if(typeof(depth)=='undefined')
		{
			var depth = 1;
		}
		amt = (120-(depth*20))
		amt = amt.toString();
		marg = amt+'px';
		jQuery(this).children('input.leftmost').css('margin-left', marg);
	});
}