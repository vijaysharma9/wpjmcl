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

	// Datepicker
	$( "input#_company_expires" ).datepicker({
		dateFormat: 'yy-mm-dd',
		minDate: 0
	});

	// Settings
	$('.job-manager-settings-wrap')
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
		.on( 'change', '#setting-company_listings_linkedin_import', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-job_manager_linkedin_api_key').closest('tr').show();
			} else {
				$('#setting-job_manager_linkedin_api_key').closest('tr').hide();
			}
		})
		.on( 'change', '#setting-company_listings_enable_registration', function() {
			if ( $( this ).is(':checked') ) {
				$('#setting-company_listings_generate_username_from_email, #setting-company_listings_registration_role').closest('tr').show();
			} else {
				$('#setting-company_listings_generate_username_from_email, #setting-company_listings_registration_role').closest('tr').hide();
			}
		});

	$('#setting-company_listings_enable_skills, #setting-company_listings_enable_categories, #setting-company_listings_linkedin_import, #setting-company_listings_enable_registration').change();

	var
		$elmCmpText,
		underscore;

	if ( jq('#_company_name').length ) {
		$elmCmpText = jq('#_company_name');
		underscore  = '_';
	} else if ( jq('#company_name').length ) {
		$elmCmpText = jq('#company_name');
		underscore  = '';
	}

	if ( $elmCmpText ) {

		// Ajax customer search boxes
		var select2_args = {
			minimumInputLength: '3',
			initSelection : function (element, callback) {
				var pre_filled = element.val();
				if ( 0 < pre_filled.length ) {
					callback({id: '1', text: pre_filled});
				}
			},
			//Allow manually entered text in drop down.
			createSearchChoice:function(term, data) {
				if ( jq(data).filter( function() {
						return this.text.localeCompare(term)===0;
					}).length===0) {
					return {id:0, text:term};
				}
			},
			ajax: {
				url: ajaxurl,
				dataType: 'json',
				quietMillis: 250,
				data: function (term) {
					return {
						term: term,
						action: 'company_listings_json_search_company',
					};
				},
				results: function (data) {
					var terms = [];
					if (data) {
						jq.each(data, function (id, val) {
							terms.push({
								id: id,
								text: val.title
							});
						});
					}
					return {results: terms};
				},
				cache: true
			},

		};

		$elmCmpText.select2(select2_args);

		$elmCmpText.on( 'change', function(e) {

			var
				company_id      = $elmCmpText.val(),
				elmCmpnyLocation= jq('#'+underscore+'job_location'),
				elmCmpnyWebsite = jq('#'+underscore+'company_website'),
				elmCmpnyTagline = jq('#'+underscore+'company_tagline'),
				elmCmpnyTwiiter = jq('#'+underscore+'company_twitter'),
				elmCmpnyVideo   = jq('#'+underscore+'company_video');

			//We are creating new company from an user input
			if ( company_id == 0 ) {
				jq('#_company_id').val('new');
				$elmCmpText.val( $elmCmpText.select2('data').text );
				return false;
			}

			var data = {
				action:     'company_listings_json_company_data',
				company_id: company_id
			};

			if ( jq('#post_ID').length ) {
				data.post_ID = jq('#post_ID').val();
			}

			jq.ajax({
				url: ajaxurl,
				dataType: 'json',
				data: data,
				beforeSend: function (jqxhr, obj) {
					[ elmCmpnyWebsite, elmCmpnyTagline, elmCmpnyTwiiter, elmCmpnyVideo ].forEach(function(element) {
						element.addClass('busy-input-gif');
					});
				},
				success: function( response ) {

					[ elmCmpnyWebsite, elmCmpnyTagline, elmCmpnyTwiiter, elmCmpnyVideo ].forEach(function(element) {
						element.removeClass('busy-input-gif');
					});

					$elmCmpText.val( $elmCmpText.select2('data').text );
					elmCmpnyLocation.val( response.location )
					elmCmpnyWebsite.val( response.website );
					elmCmpnyTagline.val( response.tagline );
					elmCmpnyTwiiter.val( response.twitter );
					elmCmpnyVideo.val( response.video );
					jq('#_job_group_id').val( response.group_id )
					jq('#_company_id').val( company_id );

					if ( response.logo_backend ) jq('#postimagediv .inside').html(response.logo_backend);
					if ( response.logo_frontend ) jq('.job-manager-uploaded-files').html(response.logo_frontend);
				},
				cache: true
			});
		});
	}
});