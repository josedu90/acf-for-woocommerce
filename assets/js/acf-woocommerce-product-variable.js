(function ($) {
    $(document).ready( function() {
        function get_acf_form_group(variation_id = null) {
            if (variation_id) {
                $.post(
                    acf_for_woo_obj.url,
                    {
                       action: 'acf_for_woo_ajax_change',             // POST data, action
                       variation_id: variation_id,
                    },
                    function(data) {
                        var html = '';
                        var hook = '';
                        
                        if ( data != null ) {
                            hook = data.hook;
                            html = '<div class="acf-form-field-container">' + data.html + '</div>';
                        }

                        switch (hook) {
                            case 'woocommerce_before_add_to_cart_form' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertBefore('form.variations_form');
                                break;
                            }
                            case 'woocommerce_before_variations_form' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertBefore('table.variations');
                                break;
                            }
                            case 'woocommerce_before_add_to_cart_button' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertBefore('div.single_variation_wrap');
                                break;
                            }
                            case 'woocommerce_before_single_variation' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertBefore('div.single_variation');
                                break;
                            }
                            case 'woocommerce_single_variation' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertAfter("woocommerce-variation-availability");
                                break;
                            }
                            case 'woocommerce_after_single_variation' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertAfter('div.single_variation');
                                break;
                            }
                            case 'woocommerce_after_add_to_cart_button' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertAfter('div.single_variation_wrap');
                                break;
                            }
                            case 'woocommerce_after_variations_form' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertAfter('table.variations');
                                break;
                            }
                            case 'woocommerce_after_add_to_cart_form' : {
                                $("div.product").find('.acf-form-field-container').remove();
                                $(html).insertAfter('form.variations_form');
                                break;
                            }
                            default : {
                                $("div.product").find('.acf-form-field-container').remove();
                            }
                        }

                        $('form.variations_form').find('#acf-form-data').remove();
                    },
                    'json'
                );
            }
        }

        setTimeout(function() {
            var variation_id = $('input.variation_id').val();
            var acf_fields = $('form.variations_form > .acf-form-fields').length;
            var acf_form_field_container = $('.acf-form-field-container').length;
            $('form.variations_form').find('#acf-form-data').remove();
            
            if ( acf_fields == 0 || acf_form_field_container > 0 ) {
            	get_acf_form_group(variation_id);
            }
        },10);

        $(document).on('change', 'form.variations_form .variations select', function() {
            var variation_id = $('.variation_id').val();            
            var acf_fields = $('form.variations_form > .acf-form-fields').length;
            var acf_form_field_container = $('.acf-form-field-container').length;
            
            if ( acf_fields == 0 || acf_form_field_container > 0 ) {
            	get_acf_form_group(variation_id);
            }
        });    
    })
})(jQuery);