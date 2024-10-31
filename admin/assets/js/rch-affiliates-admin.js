jQuery(function() {
    jQuery('.rchp-date').datepicker({
        defaultDate: '',
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true
    });

    jQuery('#rchp-affiliates-report-form').on('submit', function(e) {
        rchpAdminAffiliatesReport();

        e.preventDefault();
        return false;
    });

    jQuery('#rchp-products-report-form').on('submit', function(e) {
        rchpAdminProductsReport();

        e.preventDefault();
        return false;
    });

    jQuery('#rchp-create-affiliate-form').on('submit', function(e) {
        rchpAdminCreateAffiliate();

        e.preventDefault();
        return false;
    });

    jQuery('#rchp-save-commissions-form').on('submit', function(e) {
        rchpAdminSaveCommissions();

        e.preventDefault();
        return false;
    });

    jQuery('#rchp-edit-affiliate-form').on('submit', function(e) {
        rchpAdminEditAffiliate();

        e.preventDefault();
        return false;
    });

    jQuery('#rchp-save-settings-form').on('submit', function(e) {
        rchpAdminSaveSettings();

        e.preventDefault();
        return false;
    });

    jQuery.post(ajaxurl, {'action': 'rchp-check-license'}, function(registration) {
        if (!registration.active) {
            jQuery('.rchp-main-content').css('display', 'none');
            jQuery('#rchp-activation-main').css('display', 'inline-block');
            jQuery('#rchp-license-key').focus();

            if (registration.error !== '') {
                jQuery('.rchp-activation-error').text(registration.error).removeClass('rchp-hidden');
            }
        }
    });

    // Hide the default meta fields and prevent Enter from submitting the form.
    jQuery('#rchp-license-key').keydown(function(e){
        if (e.keyCode == 13) {
            rchpAdminActivate();
            e.preventDefault();
            return false;
        }
    });

    jQuery('#rchp-activate-license').click(function(e) {
        rchpAdminActivate();
        e.preventDefault();
        return false;
    });

    jQuery('.rchp-section-item').click(function(e) {
        jQuery('.rchp-section-item').removeClass('rchp-section-item-selected');
        jQuery(this).addClass('rchp-section-item-selected');
        var section = jQuery(this).attr('data-section');
        jQuery('.rchp-section').hide();
        jQuery('#rchp-section-' + section).show();
    });

    jQuery('#rchp-create-code-automatically').on('change', function() {
        jQuery('#rchp-create-manual-code-container').toggle(jQuery(this).checked);
    });

    jQuery('.rchp-edit-affiliate-cancel').off('click.rcode').on('click.rcode', function(e) {
        rchpHideEditAffiliateDialog();

        e.preventDefault();
        return false;
    });

    jQuery('.rchp-edit-affiliate-delete').off('click.rcode').on('click.rcode', function(e) {
        rchpDeleteAffiliate();

        e.preventDefault();
        return false;
    });

    rchpAdminAffiliatesReport();

    jQuery('.rchp-alphanumeric').bind('keyup blur',function() {
        var node = jQuery(this);
        node.val(node.val().replace(/[^a-zA-Z0-9\-_]/g, ''));
    });
});

function rchpAdminSaveSettings() {
    var messageContainer = jQuery('#rchp-save-settings-message');
    var saveButton = jQuery('#rchp-save-settings-button');
    var form = jQuery('#rchp-save-settings-form').serialize();

    saveButton.prop('disabled', true);
    messageContainer.html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-save-settings', 'form': form, 'security': rchp.nonces.saveSettings }, function(result) {
        rchpAdminAffiliatesReport();
        saveButton.prop('disabled', false);
        messageContainer.html(result.data.html);
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.prop('disabled', false);
        if (errorThrown) {
            messageContainer.html(errorThrown);
        } else {
            messageContainer.text('Unknown ajax error');
        }
    });
}

function rchpAdminActivate() {
    jQuery('.rchp-activation-error').text('');
    jQuery('#rchp-activate-license').prop('disabled', true).val(rchp.i18n.activating);

    jQuery.post(ajaxurl, {'action': 'rchp-activation', 'license-key': jQuery('#rchp-license-key').val() }, function(result) {
        if (result.active === true) {
            location.reload();
        } else {
            jQuery('.rchp-activation-error').text(result.error);
            jQuery('#rchp-activate-license').prop('disabled', false).val('Activate');
        }
    });
}

function rchpAdminAffiliatesReport() {
    var form = jQuery('#rchp-affiliates-report-form');
    var reports = jQuery('#rchp-affiliates-report-results');
    reports.html('<div class="rchp-admin-top-text">' + rchp.i18n.loading + '</div><i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-affiliates-report', 'form': form.serialize(), 'security': rchp.nonces.affiliatesReport}, function(result) {
        reports.html(result.data.html);

        rchpAdminBindSortableColumns(form, '#rchp-affiliates-table');
        rchpAdminBindExportButton();

        if (jQuery('#rchp-products-report-results').text() == '') {
            rchpAdminProductsReport();
        }

        jQuery('.rchp-view-orders').off('click.rcode').on('click.rcode', function(e) {
            var affiliate = jQuery(this).closest('tr').attr('data-code');
            var ordersUrl = rchp.ordersUrl + '&rch_affiliate=' + affiliate;

            var win = window.open(ordersUrl, '_blank');
            win.focus();

            e.preventDefault();
            return false;
        });

        jQuery('.rchp-copy-url').off('hover.rcode').on('hover.rcode', function(e) {
            var icon = jQuery(this).closest('td').find('.fa-copy');
            if (icon.css('visibility') == 'visible') {
                icon.css('visibility', 'hidden');
            } else {
                icon.css('visibility', 'visible');
            }
        });

        jQuery('.rchp-copy-url').off('click.rcode').on('click.rcode', function(e) {
            var temp = jQuery('<input>');
            jQuery('body').append(temp);
            temp.val(jQuery(this).attr('href')).select();
            document.execCommand('copy');
            temp.remove();

            var message = jQuery('<div style="color: blue;">' + rchp.i18n.linkCopied + '</div>');
            jQuery(this).closest('td').append(message);
            message.fadeOut(2000);

            e.preventDefault();
            return false;
        });

        jQuery('.rchp-edit-affiliate').off('click.rcode').on('click.rcode', function(e) {
            var id = jQuery(this).closest('tr').attr('data-id');
            var code = jQuery(this).closest('tr').attr('data-code');
            var name = jQuery(this).closest('tr').attr('data-name');
            var userId = jQuery(this).closest('tr').attr('data-user_id');
            var commission = jQuery(this).closest('tr').attr('data-commission');

            rchpAdminShowEditAffiliateDialog(id, code, name, userId, commission);

            e.preventDefault();
            return false;
        });

    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            reports.text(errorThrown);
        } else {
            reports.text('Unknown error');
        }
    });
}

function rchpAdminProductsReport() {
    var form = jQuery('#rchp-products-report-form');
    var reports = jQuery('#rchp-products-report-results');
    reports.html('<div class="rchp-admin-top-text">' + rchp.i18n.loading + '</div><i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-products-report', 'form': form.serialize(), 'security': rchp.nonces.productsReport}, function(result) {
        reports.html(result.data.html);

        rchpAdminBindSortableColumns(form, '#rchp-products-table');
        rchpAdminBindExportButton();

    }).fail(function(xhr, textStatus, errorThrown) {
        if (errorThrown) {
            reports.text(errorThrown);
        } else {
            reports.text('Unknown error');
        }
    });
}

function rchpAdminBindExportButton() {
    jQuery('.rchp-export-button').off('click.rcode').on('click.rcode', function(e) {
        var exportButton = jQuery(this);
        var waitMessage = '<div class="rchp-admin-exporting-message"><i class="fas fa-cog fa-spin"></i> ' + rchp.i18n.exporting + '</div>';

        exportButton.after(waitMessage);
        exportButton.prop('disabled', true);

        var formId = '#rchp-affiliates-report-form';
        if (exportButton.attr('id') == 'rchp-products-report-export-button') {
            formId = '#rchp-products-report-form';
        }
        var form = jQuery(formId).serialize();

        jQuery.post(ajaxurl, {'action': 'rchp-export-report', 'form': form, 'security': rchp.nonces.exportReport}, function(result) {
            var url = rchp.exportUrl + '&action=rchp_export&report_type=' + result.data.report_type + '&filename=' + result.data.output_filename;
            window.open( url, '_self');

            exportButton.prop('disabled', false);
            jQuery('.rchp-admin-exporting-message').remove();

        }).fail(function(xhr, textStatus, errorThrown) {
            exportButton.prop('disabled', false);
            jQuery('.rchp-admin-exporting-message').remove();

            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown error');
            }
        });

        e.preventDefault();
        return false;
    });
}

function rchpAdminBindSortableColumns(form, table) {
    jQuery(table).find('.rchp-admin-table-sortable-column').off('click.rcode').on('click.rcode', function(e) {
        var column = jQuery(this).attr('data-column');
        var order = 'asc';

        if (jQuery(this).find('.rchp-sort').hasClass('fa-sort-down')) {
            order = 'desc';
        }

        jQuery(form).find('[name="sort"]').val(column);
        jQuery(form).find('[name="sort_order"]').val(order);

        jQuery(form).submit();
    });

    jQuery('.rchp-admin-table-sortable-column').off('hover.rcode').on('hover.rcode', function(e) {
        var sortIcon = jQuery(this).find('.rchp-sort');

        if (sortIcon.hasClass('rchp-invisible')) {
            if ( sortIcon.css('visibility') == 'hidden' ) {
                sortIcon.css('visibility', 'visible');
            } else {
                sortIcon.css('visibility', 'hidden');
            }
        }

        if (sortIcon.hasClass('fa-sort-down')) {
            sortIcon.removeClass('fa-sort-down').addClass('fa-sort-up');
        } else {
            sortIcon.removeClass('fa-sort-up').addClass('fa-sort-down');
        }
    });
}

function rchpAdminCreateAffiliate() {
    var form = jQuery('#rchp-create-affiliate-form');
    var messageContainer = form.find('.rchp-admin-message');
    var saveButton = jQuery('#rchp-create-affiliate-save-button');

    saveButton.hide();
    messageContainer.removeClass('rchp-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-create-affiliate', 'form': form.serialize(), 'security': rchp.nonces.createAffiliate}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            setTimeout(function() {
                messageContainer.text('');
            }, 5000);
            form[0].reset();
            jQuery('#rchp-create-manual-code-container').hide();
            jQuery('#rchp-affiliates-report-form').submit();
            saveButton.show();
        } else {
            messageContainer.addClass('rchp-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('rchp-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('rchp-admin-error').text('Unknown Error');
        }
    });
}

function rchpAdminEditAffiliate() {
    var form = jQuery('#rchp-edit-affiliate-form');
    var messageContainer = form.find('.rchp-admin-message');
    var saveButton = jQuery('#rchp-edit-affiliate-save-button');

    saveButton.hide();
    messageContainer.removeClass('rchp-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-edit-affiliate', 'form': form.serialize(), 'security': rchp.nonces.editAffiliate}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            jQuery('#rchp-affiliates-report-form').submit();
            saveButton.show();
            rchpHideEditAffiliateDialog();
        } else {
            messageContainer.addClass('rchp-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('rchp-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('rchp-admin-error').text('Unknown Error');
        }
    });
}

function rchpDeleteAffiliate() {
    if (confirm(rchp.i18n.confirmDelete)) {
        var affiliateId = jQuery('#rchp-edit-affiliate-id').val();

        jQuery.post(ajaxurl, {'action': 'rchp-delete-affiliate', 'affiliate_id': affiliateId, 'security': rchp.nonces.deleteAffiliate}, function(result) {
            if (result.success) {
                jQuery('#rchp-affiliates-report-form').submit();
                rchpHideEditAffiliateDialog();
            } else {
                alert(result.data.message);
            }
        }).fail(function(xhr, textStatus, errorThrown) {
            if (errorThrown) {
                alert(errorThrown);
            } else {
                alert('Unknown Error');
            }
        });
    }
}

function rchpAdminShowEditAffiliateDialog(id, code, name, userId, commission) {
    var form = jQuery('#rchp-edit-affiliate-form');
    var messageContainer = form.find('.rchp-admin-message');
    var section = jQuery('#rchp-section-affiliates-report');

    messageContainer.text('');

    var overlay = jQuery('<div id="rchp-overlay">');
    jQuery('body').append(overlay);
    overlay.css('top', jQuery('#wpadminbar').height() + 'px');
    overlay.css('left', jQuery('#adminmenuwrap').width() + 'px');

    var dialog = jQuery('#rchp-edit-affiliate-container');
    dialog.css('position', 'fixed');
    dialog.css('display', 'inline');
    dialog.css('height', '');
    dialog.css('width', '');
    dialog.css('left', section.position().left + (section.width() / 2) - dialog.width());
    dialog.css('top', section.position().top);

    if (!userId) {
        userId = -1;
    }

    jQuery('#rchp-edit-affiliate-id').val(id);
    jQuery('#rchp-edit-code').val(code);
    jQuery('#rchp-edit-name').val(name);
    jQuery('#rchp-edit-user_id').val(userId);
    jQuery('#rchp-edit-commission').val(commission);

    overlay.css('display', 'block');
    dialog.css('display', 'block');
}

function rchpHideEditAffiliateDialog() {
    var form = jQuery('#rchp-edit-affiliate-form');
    form[0].reset();

    jQuery('#rchp-edit-affiliate-container').hide();
    jQuery('#rchp-overlay').remove();
}

function rchpAdminSaveCommissions() {
    var form = jQuery('#rchp-save-commissions-form');
    var messageContainer = form.find('#rchp-save-commissions-message');
    var saveButton = jQuery('#rchp-save-commissions-button');

    saveButton.hide();
    messageContainer.removeClass('rchp-admin-error').html('<i class="fas fa-cog fa-spin fa-3x"></i>');

    jQuery.post(ajaxurl, {'action': 'rchp-save-commissions', 'form': form.serialize(), 'security': rchp.nonces.saveCommissions}, function(result) {
        if (result.success) {
            messageContainer.text(result.data.message);
            setTimeout(function() {
                messageContainer.text('');
            }, 5000);
            form[0].reset();
            saveButton.show();
        } else {
            messageContainer.addClass('rchp-admin-error').text(result.data.message);
            saveButton.show();
        }
    }).fail(function(xhr, textStatus, errorThrown) {
        saveButton.show();
        if (errorThrown) {
            messageContainer.addClass('rchp-admin-error').text(errorThrown);
        } else {
            messageContainer.addClass('rchp-admin-error').text('Unknown Error');
        }
    });
}
