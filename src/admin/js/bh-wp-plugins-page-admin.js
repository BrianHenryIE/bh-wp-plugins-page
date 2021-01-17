(function( $ ) {

	// I think I delayed this so the other handlers would be added first.
	setTimeout("removeClickHandlers()", 500);

})( jQuery );

function removeClickHandlers ( )
{
	jQuery('.deactivate a').each(function(){
		jQuery(this).unbind('click');
	});
}
