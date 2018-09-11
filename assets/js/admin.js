if ( typeof jq == "undefined" ) {
	var jq = jQuery;
}

jQuery(document).ready(function($) {
	// Data rows
	$( "input.company_listings_add_row" ).click(function(){
		$(this).closest('table').find('tbody').append( $(this).data('row') );
		return false;
	});

	// Sorting
	$('.wc-job-manager-company-listings-repeated-rows tbody').sortable({
		items:'tr',
		cursor:'move',
		axis:'y',
		handle: 'td.sort-column',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65
	});

	// Settings
	$('.wp-job-manager-company-listings-settings-wrap')
		.on( 'change', '#setting-company_listings_enable_skills', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_listings_max_skills').closest('tr').show();
			} else {
				$('#setting-company_listings_max_skills').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_listings_enable_categories', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_listings_enable_default_category_multiselect, #setting-company_listings_category_filter_type').closest('tr').show();
			} else {
				$('#setting-company_listings_enable_default_category_multiselect, #setting-company_listings_category_filter_type').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_listings_enable_registration', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_listings_generate_username_from_email, #setting-company_listings_registration_role').closest('tr').show();
			} else {
				$('#setting-company_listings_generate_username_from_email, #setting-company_listings_registration_role').closest('tr').hide();
			}
		});

	$('#setting-company_listings_enable_skills, #setting-company_listings_enable_categories, #setting-company_listings_enable_registration').change();
});
