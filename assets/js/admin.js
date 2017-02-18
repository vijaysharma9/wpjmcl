jQuery(document).ready(function($) {
	// Data rows
	$( "input.company_manager_add_row" ).click(function(){
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

	// Datepicker
	$( "input#_company_expires" ).datepicker({
		dateFormat: 'yy-mm-dd',
		minDate: 0
	});

	// Settings
	$('.job-manager-settings-wrap')
		.on( 'change', '#setting-company_manager_enable_skills', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_manager_max_skills').closest('tr').show();
			} else {
				$('#setting-company_manager_max_skills').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_manager_enable_categories', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_manager_enable_default_category_multiselect, #setting-company_manager_category_filter_type').closest('tr').show();
			} else {
				$('#setting-company_manager_enable_default_category_multiselect, #setting-company_manager_category_filter_type').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_manager_linkedin_import', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-job_manager_linkedin_api_key').closest('tr').show();
			} else {
				$('#setting-job_manager_linkedin_api_key').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_manager_enable_registration', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_manager_generate_username_from_email, #setting-company_manager_registration_role').closest('tr').show();
			} else {
				$('#setting-company_manager_generate_username_from_email, #setting-company_manager_registration_role').closest('tr').hide();
			}
		});

	$('#setting-company_manager_enable_skills, #setting-company_manager_enable_categories, #setting-company_manager_linkedin_import, #setting-company_manager_enable_registration').change();
});