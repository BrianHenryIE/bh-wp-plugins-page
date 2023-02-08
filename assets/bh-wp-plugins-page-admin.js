(function( $ ) {

	// I think I delayed this so the other handlers would be added first.
	setTimeout("removeClickHandlers()", 500);

	$( function() {
		// tr.active:nth-child(1) > td:nth-child(2) > strong:nth-child(1)
		//
		$('td.plugin-title.column-primary strong').click(function (e) {
			console.log('hello');
			console.log(e);
			let pluginName = e.target.innerHTML;

			// Get the tr's `data-slug`/`data-plugin`.
			var pluginBasename = $(this).closest('tr').data('plugin');

			var input = window.prompt('Enter a new name for: ' + pluginName);

			// Look up the JSON in the HTML, since we're not able to manipulate the <tr> data to add more (in ::single_row())
			// If the input is empty but it has never been changed before, do nothing.
			// If the input is empty and it has been changed before, reset it to the original.


			// console.log( pluginBasename );
			// console.log( pluginName );
			// console.log( response );

			const data = {
				action: 'bh_wp_plugins_page_set_plugin_name',
				// _ajax_nonce: nonce,
				pluginName: input,
			};

			$.post( ajaxUrl, data, function( response ) {

				const newData = response.data;

				// TODO: Update the plugin name
				$(this).innerHTML = response;

			} );


		});

	});

})( jQuery );

function removeClickHandlers ( )
{
	jQuery('.deactivate a').each(function(){
		jQuery(this).unbind('click');
	});
}
