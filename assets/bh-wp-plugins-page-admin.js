(function( $ ) {

	// I think I delayed this so the other handlers would be added first.
	setTimeout("removeClickHandlers()", 500);

	$( function() {

		// jQuery('.plugin-title a:contains("Uninstall")').parent().remove();

		$('td.plugin-title.column-primary strong').click(function (e) {

			const titleElement = $(this);
			let pluginName = e.target.innerHTML;

			// Get the tr's `data-slug`/`data-plugin`.
			var pluginBasename = titleElement.closest('tr').data('plugin');

			// How to capture cancel?
			var input = window.prompt('Enter a new name for: ' + pluginName);

			// Look up the JSON in the HTML, since we're not able to manipulate the <tr> data to add more (in ::single_row())
			// If the input is empty but it has never been changed before, do nothing.
			// If the input is empty and it has been changed before, reset it to the original.

			const ajaxUrl = bh_wp_plugins_page_ajax_data.ajaxUrl;
			const nonce = bh_wp_plugins_page_ajax_data.nonce;

			const data = {
				action: 'bh_wp_plugins_page_set_plugin_name',
				_ajax_nonce: nonce,
				pluginName: input,
				pluginBasename: pluginBasename,
			};

			$.post( ajaxUrl, data, function( response ) {

				const newData = response.data;

				titleElement.text(newData.plugin_name);
			} );

		});

		// Enclose every plugin version in a .plugin-version span.
		$('.plugin-version-author-uri').each(function(e){
			let updated = $(this).html().replace(/(Version.*?) \|/,'<span class="plugin-version">$1</span> |');
			jQuery(this).html(updated);
		});

	});

})( jQuery );

function removeClickHandlers ( )
{
	jQuery('.deactivate a').each(function(){
		jQuery(this).unbind('click');
	});
}
