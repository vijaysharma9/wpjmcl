jQuery(document).ready(function($) {

	$('.company-dashboard-action-delete').click(function() {
		var answer = confirm( company_listings_company_dashboard.i18n_confirm_delete );

		if (answer)
			return true;

		return false;
	});

});