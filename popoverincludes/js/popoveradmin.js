function popoverremoveaction() {
	var section = jQuery(this).attr('id');
	var sectionname = section.replace('remove-','');

	jQuery('#main-' + sectionname).appendTo('#hiden-actions');
	jQuery('#' + sectionname).show();

	// Move from the fields
	jQuery('#in-positive-rules').val( jQuery('#in-positive-rules').val().replace(',' + sectionname, ''));

	return false;
}

function popoverremovemessage() {

	jQuery('#upmessage').remove();
	return false;
}

function popoverReady() {

	jQuery('.popover-draggable').draggable({
			opacity: 0.7,
			helper: 'clone',
			start: function(event, ui) {
					jQuery('input#beingdragged').val( jQuery(this).attr('id') );
				 },
			stop: function(event, ui) {
					jQuery('input#beingdragged').val( '' );
				}
				});

	jQuery('.droppable-rules').droppable({
			hoverClass: 'hoveringover',
			drop: function(event, ui) {
					moving = jQuery('input#beingdragged').val();
					ruleplace = jQuery(this).attr('id');
					if(moving != '') {
						jQuery('#main-' + moving).appendTo('#' + ruleplace + '-holder');
						jQuery('#' + moving).hide();

						// put the name in the relevant holding input field
						jQuery('#in-' + ruleplace).val( jQuery('#in-' + ruleplace).val() + ',' + moving );
					}
				}
	});

	jQuery('#positive-rules-holder').sortable({
		opacity: 0.7,
		helper: 'clone',
		placeholder: 'placeholder-rules',
		update: function(event, ui) {
				jQuery('#in-positive-rules').val(',' + jQuery('#positive-rules-holder').sortable('toArray').join(',').replace(/main-/gi, ''));
			}
	});

	jQuery('a.removelink').click(popoverremoveaction);
	jQuery('a#closemessage').click(popoverremovemessage);


}

jQuery(document).ready(popoverReady);