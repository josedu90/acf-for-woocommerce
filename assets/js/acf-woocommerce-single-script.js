(function( $ ) {
    'use strict';

    $(document).ready(function(){

        var ins = new acf.Model({
            actions: {
                'validation_success': 'success',
                'validation_begin' : 'begin'
            },
            begin: function($form) {
                $form.find('[name="add-to-cart"]').attr('name', 'add-to-cart-acf');
            },
            success: function($form){
                $form.find('[name="add-to-cart-acf"]').attr('name', 'add-to-cart');
                var id = $('.single_add_to_cart_button').val();
                $('.single_add_to_cart_button').after('<input type="hidden" name="add-to-cart" value="'+ id +'">');
            }
        });

        $('.acf-basic-uploader input').on('change', function(event){
            ACFFWuploadProgress(event, $(this));
        });
    });
})( jQuery );



function ACFFWuploadProgress(event, el) {
    var $ = jQuery;

    var form = new FormData();
    var files = $(el).prop('files');
    if (files.length > 0) {
        $.each(files, function (index, file) {
            form.append($(el).attr('name'), file);
        });
    }

    $.ajax({
        method: 'post',
        data: form,
        async: false,
        cache: false,
        contentType: false,
        beforeSend: function(){
            acf.validation.lockForm($(el).closest('form'));
        },
        processData: false,
        url: wc_cart_fragments_params.ajax_url + '?action=upload_ajax',
        success: function (data) {
            if (data.success == true) {
                $.each(data.data, function (index, value) {
                    var $input = $('#acf-' + value.key);
                    var $wrapPreview = $('[name="acf[' + value.key + ']"]').closest('.acf-input');
                    $input.val('');
                    $('[type="hidden"][name="acf[' + value.key + ']"]').val(value.attachment_id);
                    $wrapPreview.addClass('has-value');
                    $wrapPreview.find('img').attr('src', value.src);
                    $wrapPreview.find('[data-name="filename"]').text(value.post_title);
                    $wrapPreview.find('[data-name="filesize"]').text(value.size);
                });
            }
        },
        error: function () {

        },
        complete: function () {
            acf.validation.unlockForm($(el).closest('form'));
        }
    });

}