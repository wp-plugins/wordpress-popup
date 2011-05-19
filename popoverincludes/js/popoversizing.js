function sizeReady() {
	jQuery('#messagebox').width(jQuery('#message').width());
	jQuery('#messagebox').height(jQuery('#message').height());

	jQuery('#messagebox').css('top', (jQuery(window).height() / 2) - (jQuery('#message').height() / 2) );
	jQuery('#messagebox').css('left', (jQuery(window).width() / 2) - (jQuery('#message').width() / 2) );
}

jQuery(window).load(sizeReady);