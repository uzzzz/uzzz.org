jQuery(window).load(function () {
    jQuery('#subscribe_thickbox').trigger('click');
    jQuery("#TB_closeWindowButton").click(function () {
        jQuery.post(ajaxurl, {
            'action': 'close_tab'
        });
    });
});


jQuery('document').ready(function () {

    // deactivation popup code
    var tdrd_plugin_admin = jQuery('.documentation_tdrd_plugin').closest('div').find('.deactivate').find('a');
    tdrd_plugin_admin.click(function (event) {
        event.preventDefault();
        jQuery('#deactivation_thickbox_tdrd').trigger('click');
        jQuery('#TB_window').removeClass('thickbox-loading');
        change_thickbox_size_tdrd();
    });
    checkOtherDeactivate();
    jQuery('.sol_deactivation_reasons').click(function () {
        checkOtherDeactivate();
    });
    jQuery('#sbtDeactivationFormClosetdrd').click(function (event) {
        event.preventDefault();
        jQuery("#TB_closeWindowButton").trigger('click');
    });

    jQuery('.tdrd-deactivation').on('click', function() {
        window.location.href = tdrd_plugin_admin.attr('href');
    });


    jQuery("input[name='txtPermissiontdrd']").change(function () {
        if (jQuery(this).is(':checked')) {
            jQuery(".subscribe_widget input[name='sbtEmail']").removeAttr('disabled');
        } else {
            jQuery(".subscribe_widget input[name='sbtEmail']").attr('disabled', 'disabled');
        }
    });
});

function tdrd_show_hide_permission() {
    jQuery('.tdrd_permission_cover').slideToggle();
}

function tdrd_submit_optin(options) {
    result = {};
    result.action = 'tdrd_submit_optin';
    result.email = jQuery('#tdrd_admin_email').val();
    result.type = options;

    if (options == 'submit') {
        if (jQuery('input#tdrd_agree_gdpr').is(':checked')) {
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: result,
                error: function () { },
                success: function () {
                    window.location.href = "admin.php?page=trash_duplicates";
                },
                complete: function () {
                    window.location.href = "admin.php?page=trash_duplicates";
                }
            });
        }
        else {
            jQuery('.tdrd_agree_gdpr_lbl').css('color', '#ff0000');
        }
    }
    else if (options == 'deactivate') {
        if (jQuery('input#tdrd_agree_gdpr_deactivate').is(':checked')) {
            var tdrd_plugin_admin = jQuery('.documentation_tdrd_plugin').closest('div').find('.deactivate').find('a');
            result.selected_option_de = jQuery('input[name=sol_deactivation_reasons_tdrd]:checked', '#frmDeactivationtdrd').val();
            result.selected_option_de_id = jQuery('input[name=sol_deactivation_reasons_tdrd]:checked', '#frmDeactivationtdrd').attr("id");
            result.selected_option_de_text = jQuery("label[for='" + result.selected_option_de_id + "']").text();
            result.selected_option_de_other = jQuery('.sol_deactivation_reason_other_tdrd').val();
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: result,
                error: function () { },
                success: function () {
                    window.location.href = tdrd_plugin_admin.attr('href');
                },
                complete: function () {
                    window.location.href = tdrd_plugin_admin.attr('href');
                }
            });
        }
        else {
            jQuery('.tdrd_agree_gdpr_lbl').css('color', '#ff0000');
        }
    }
    else {
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            data: result,
            error: function () { },
            success: function () {
                window.location.href = "admin.php?page=trash_duplicates";
            },
            complete: function () {
                window.location.href = "admin.php?page=trash_duplicates";
            }
        });
    }
}

function change_thickbox_size_tdrd() {
    jQuery(document).find('#TB_window').width('700').height('400').css('margin-left', -700 / 2);
    jQuery(document).find('#TB_ajaxContent').width('640');
    var doc_height = jQuery(window).height();
    var doc_space = doc_height - 400;
    if (doc_space > 0) {
        jQuery(document).find('#TB_window').css('margin-top', doc_space / 2);
    }
}

function checkOtherDeactivate() {
    var selected_option_de = jQuery('input[name=sol_deactivation_reasons_tdrd]:checked', '#frmDeactivationtdrd').val();
    if (selected_option_de == '7') {
        jQuery('.sol_deactivation_reason_other_tdrd').val('');
        jQuery('.sol_deactivation_reason_other_tdrd').show();
    }
    else {
        jQuery('.sol_deactivation_reason_other_tdrd').val('');
        jQuery('.sol_deactivation_reason_other_tdrd').hide();
    }
}