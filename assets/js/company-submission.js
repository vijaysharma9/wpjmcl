jQuery(document).ready(function($) {
	$( '.company-manager-add-row' ).click(function() {
		var $wrap     = $(this).closest('.field');
		var max_index = 0;

		$wrap.find('input.repeated-row-index').each(function(){
			if ( parseInt( $(this).val() ) > max_index ) {
				max_index = parseInt( $(this).val() );
			}
		});

		var html          = $(this).data('row').replace( /%%repeated-row-index%%/g, max_index + 1 );
		$(this).before( html );
		return false;
	});
	$( '#submit-company-form' ).on('click', '.company-manager-remove-row', function() {
		if ( confirm( company_manager_company_submission.i18n_confirm_remove ) ) {
			$(this).closest( 'div.company-manager-data-row' ).remove();
		}
		return false;
	});
	$( '#submit-company-form' ).on('click', '.job-manager-remove-uploaded-file', function() {
		$(this).closest( '.job-manager-uploaded-file' ).remove();
		return false;
	});
	$('.fieldset-company_experience .field, .fieldset-company_education .field, .fieldset-links .field').sortable({
		items:'.company-manager-data-row',
		cursor:'move',
		axis:'y',
		scrollSensitivity:40,
		forcePlaceholderSize: true,
		helper: 'clone',
		opacity: 0.65
	});

	// Confirm navigation
	var confirm_nav = false;

	if ( $('form#company_preview').size() ) {
		confirm_nav = true;
	}
	$( 'form#submit-company-form' ).on( 'change', 'input', function() {
		confirm_nav = true;
	});
	$( 'form#submit-company-form, form#company_preview' ).submit(function(){
		confirm_nav = false;
		return true;
	});
	$(window).bind('beforeunload', function(event) {
		if ( confirm_nav ) {
			return company_manager_company_submission.i18n_navigate;
		}
	});

	// Linkedin import
	$('input.import-from-linkedin').click(function() {
		if ( IN.User.isAuthorized() ) {
			import_linkedin_company_data();
		} else {
			IN.Event.on( IN, "auth", import_linkedin_company_data );
			IN.UI.Authorize().place();
		}
		return false;
	});

	function import_linkedin_company_data() {
		$( 'fieldset.import-from-linkedin' ).remove();
		IN.API.Profile("me")
			.fields(
				[
					"firstName",
					"lastName",
					"formattedName",
					"headline",
					"summary",
					"specialties",
					"associations",
					"interests",
					"pictureUrl",
					"publicProfileUrl",
					"emailAddress",
					"location:(name)",
					"dateOfBirth",
					"threeCurrentPositions:(title,company,summary,startDate,endDate,isCurrent)",
					"threePastPositions:(title,company,summary,startDate,endDate,isCurrent)",
					"positions:(title,company,summary,startDate,endDate,isCurrent)",
					"educations:(schoolName,degree,fieldOfStudy,startDate,endDate,activities,notes)",
					"skills:(skill)",
					"phoneNumbers",
					"primaryTwitterAccount",
					"memberUrlResources"
				]
			)
			.result( function( result ) {
				var profile = result.values[0];
				$form       = $( '#submit-company-form' );

				$form.find('input[name="company_name"]').val( profile.formattedName );
				$form.find('input[name="company_email"]').val( profile.emailAddress );
				$form.find('input[name="company_title"]').val( profile.headline );
				$form.find('input[name="company_location"]').val( profile.location.name );

				if ( profile.summary ) {
					$form.find('textarea[name="company_content"]').val( profile.summary );

					if ( $.type( tinymce ) === 'object' ) {
						tinymce.get('company_content').setContent( profile.summary );
					}
				}

				$( profile.skills.values ).each( function( i, e ) {
					if ( $form.find('input[name="company_skills"]').val() ) {
						$form.find('input[name="company_skills"]').val( $form.find('input[name="company_skills"]').val() + ', ' + e.skill.name );
					} else {
						$form.find('input[name="company_skills"]').val( e.skill.name );
					}
				});

				$( profile.memberUrlResources.values ).each( function( i, e ) {
					if ( e.name && e.url ) {
						$( '.fieldset-links' ).find( '.company-manager-add-row' ).click();
						$( '.fieldset-links' ).find( 'input[name^="link_name"]' ).last().val( e.name );
						$( '.fieldset-links' ).find( 'input[name^="link_url"]' ).last().val( e.url );
					}
				});

				$( profile.educations.values ).each( function( i, e ) {
					var qual = [];
					var date = [];

					if ( e.fieldOfStudy ) qual.push( e.fieldOfStudy );
					if ( e.degree ) qual.push( e.degree );
					if ( e.startDate ) date.push( e.startDate.year );
					if ( e.endDate ) date.push( e.endDate.year );

					$( '.fieldset-company_education' ).find( '.company-manager-add-row' ).click();
					$( '.fieldset-company_education' ).find( 'input[name^="company_education_location"]' ).last().val( e.schoolName );
					$( '.fieldset-company_education' ).find( 'input[name^="company_education_qualification"]' ).last().val( qual.join( ', ' ) );
					$( '.fieldset-company_education' ).find( 'input[name^="company_education_date"]' ).last().val( date.join( '-' ) );
					$( '.fieldset-company_education' ).find( 'textarea[name^="company_education_notes"]' ).last().val( e.notes );
				});

				$( profile.positions.values ).each( function( i, e ) {
					var date = [];

					if ( e.startDate ) date.push( e.startDate.year );
					if ( e.endDate ) date.push( e.endDate.year );

					$( '.fieldset-company_experience' ).find( '.company-manager-add-row' ).click();
					$( '.fieldset-company_experience' ).find( 'input[name^="company_experience_employer"]' ).last().val( e.company.name );
					$( '.fieldset-company_experience' ).find( 'input[name^="company_experience_job_title"]' ).last().val( e.title );
					$( '.fieldset-company_experience' ).find( 'input[name^="company_experience_date"]' ).last().val( date.join( '-' ) );
					$( '.fieldset-company_experience' ).find( 'textarea[name^="company_experience_notes"]' ).last().val( e.summary );
				});

				if ( profile.pictureUrl ) {
					var photo_field = $('.fieldset-company_photo .field');

					if ( photo_field ) {
						var photo_field_name = photo_field.find(':input[type="file"]').attr( 'name' );
					}
					$('.fieldset-company_photo .field').prepend('<div class="job-manager-uploaded-files"><div class="job-manager-uploaded-file"><span class="job-manager-uploaded-file-preview"><img src="' + profile.pictureUrl + '" /> <a class="job-manager-remove-uploaded-file" href="#">[' + company_manager_company_submission.i18n_remove + ']</a></span><input type="hidden" class="input-text" name="current_' + photo_field_name + '" value="' + profile.pictureUrl + '" /></div></div>');
				}

				$form.trigger( 'linkedin_import', profile );
			}
		);
	}
});
