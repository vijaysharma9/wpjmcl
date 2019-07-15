if ( typeof jq == "undefined" ) {
    var jq = jQuery;
}

if (company_listings_company_field.company_field_enable_select2_search && jQuery().select2) {
    jq( function() {
        var $element = jq(company_listings_company_field.company_field_selector);

        if ($element) {
            // remove client side 'required' validation
            $element.removeAttr('required');

            // Ajax customer search boxes
            var select2_args = {
                allowClear: company_listings_company_field.company_field_allowclear,
                minimumInputLength: company_listings_company_field.company_field_minimumInputLength,
                ajax: {
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            term: params.term,
                            action: 'company_listings_json_search_company',
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data,
                        };
                    },
                    cache: true,
                },
                language: {
                    errorLoading: function() {
                        return company_listings_company_field.select2_errorLoading;
                    },
                    inputTooShort: function() {
                        return company_listings_company_field.select2_inputTooShort;
                    },
                    loadingMore: function() {
                        return company_listings_company_field.select2_loadingMore;
                    },
                    noResults: function() {
                        return company_listings_company_field.select2_noResults;
                    },
                    searching: function() {
                        return company_listings_company_field.select2_searching;
                    },
                },
            };

            $element.select2(select2_args);
        }
    });
}
