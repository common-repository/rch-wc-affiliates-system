jQuery(function() {
    jQuery('.rchp-date').datepicker({
        defaultDate: '',
        dateFormat: 'yy-mm-dd',
        numberOfMonths: 1,
        showButtonPanel: true
    });

    jQuery('.rchp-copy-url').off('click.rcode').on('click.rcode', function(e) {
        var temp = jQuery('<input>');
        jQuery('body').append(temp);
        temp.val(jQuery(this).attr('href')).select();
        document.execCommand('copy');
        temp.remove();

        var message = jQuery('<div style="color: blue;">' + rchp.i18n.linkCopied + '</div>');
        jQuery(this).append(message);
        message.fadeOut(2000);

        e.preventDefault();
        return false;
    });
});