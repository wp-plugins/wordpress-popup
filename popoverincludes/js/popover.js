function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function removeMessageBoxForever() {
	jQuery(this).parents('#messagebox').removeClass('visiblebox').addClass('hiddenbox');
	createCookie('popover_never_view', 'hidealways', 365);
	return false;
}

function removeMessageBox() {
	jQuery(this).parent('#messagebox').removeClass('visiblebox').addClass('hiddenbox');
	return false;
}

function boardReady() {
	jQuery('#clearforever').click(removeMessageBoxForever);
	jQuery('#closebox').click(removeMessageBox);

	jQuery('#message').hover( function() {jQuery('.claimbutton').removeClass('hide');}, function() {jQuery('.claimbutton').addClass('hide');});
	jQuery('#messagebox').css('visibility', 'visible');
}

jQuery(window).load(boardReady);