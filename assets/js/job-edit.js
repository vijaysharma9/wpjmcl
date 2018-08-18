if ( typeof jq == "undefined" ) {
    var jq = jQuery;
}

jq( function() {
    var $elmCmpText;
    var updateFields = true;

    if (jq('#company_id').length) {
        $elmCmpText = jq('#company_id');
    } else if (jq('#company_name').length) {
        $elmCmpText = jq('#company_name');
        updateFields = false;
    }

    if ($elmCmpText) {
        // remove client side 'required' validation
        $elmCmpText.removeAttr('required');

        // Ajax customer search boxes
        var select2_args = {
            tags: true,
            allowClear: true,
            minimumInputLength: '3',
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

        };

        $elmCmpText.select2(select2_args);

        // Only update the fields from company data when posting a job
        if (updateFields) {
            $elmCmpText.on('change', function (e, data) {

                var
                    company_id          = $elmCmpText.val(),
                    elmCmpnyLocation    = jq('#job_location'),
                    elmCmpnyWebsite     = jq('#company_website'),
                    elmCmpnyTagline     = jq('#company_tagline'),
                    elmCmpnyTwiiter     = jq('#company_twitter'),
                    elmCmpnyVideo       = jq('#company_video');

                if (parseInt(company_id) > 0) {

                    var data = {
                        action: 'company_listings_json_company_data',
                        company_id: company_id,
                    };

                    if (jq('#post_ID').length) {
                        data.post_ID = jq('#post_ID').val();
                    }

                    jq.ajax({
                        url: ajaxurl,
                        dataType: 'json',
                        data: data,
                        beforeSend: function (jqxhr, obj) {
                            [elmCmpnyWebsite, elmCmpnyTagline, elmCmpnyTwiiter, elmCmpnyVideo].forEach(function (element) {
                                element.addClass('busy-input-gif');
                            });
                        },
                        success: function (response) {

                            [elmCmpnyWebsite, elmCmpnyTagline, elmCmpnyTwiiter, elmCmpnyVideo].forEach(function (element) {
                                element.removeClass('busy-input-gif');
                            });

                            elmCmpnyLocation.val(response.location);
                            elmCmpnyWebsite.val(response.website);
                            elmCmpnyTagline.val(response.tagline);
                            elmCmpnyTwiiter.val(response.twitter);
                            elmCmpnyVideo.val(response.video);
                            jq('#_job_group_id').val(response.group_id);

                            if (response.logo_backend) jq('#postimagediv .inside').html(response.logo_backend);
                            if (response.logo_frontend) jq('.job-manager-uploaded-files').html(response.logo_frontend);
                        },
                        cache: true,
                    });

                } else {

                    // elmCmpnyLocation.val(''); // Don't change the job location if trying to create new company
                    elmCmpnyWebsite.val('');
                    elmCmpnyTagline.val('');
                    elmCmpnyTwiiter.val('');
                    elmCmpnyVideo.val('');
                    jq('#_job_group_id').val('');

                    jq('#postimagediv .inside').html('');
                    jq('.job-manager-uploaded-files').html('');

                }
            });
        }
    }
});
