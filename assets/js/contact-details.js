jQuery(document).ready(function($) {
	// Slide toggle
	jQuery( '.company_contact_details' ).hide();
	jQuery( '.company_contact_button' ).click(function() {
		jQuery( '.company_contact_details' ).slideToggle();
	});
});