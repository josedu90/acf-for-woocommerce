<?php 

class ACF_Woo_Ajax{

    function __construct()
    {
        add_action('wp_ajax_nopriv_upload_ajax', array(&$this, 'upload_ajax'));
        add_action('wp_ajax_upload_ajax',array(&$this, 'upload_ajax'));   
    }

    public function upload_ajax(){
        $upload_overrides = array('test_form' => false);
        
        $api = ACF_Woo_API::get_instance();
        $acf_field_wrapper = $api->acf_field_in_request();

        $uploadedfile = $_FILES[$acf_field_wrapper];
        $attachment = array();

        if (isset($uploadedfile['tmp_name']) && is_array($uploadedfile['tmp_name'])) {
            foreach ($uploadedfile['tmp_name'] as $key => $value) {
                $file = array(
                    "error" => $uploadedfile['error'][$key],
                    "tmp_name" => $uploadedfile['tmp_name'][$key],
                    "size" => $uploadedfile['size'][$key],
                    "name" => $uploadedfile['name'][$key],
                    "type" => $uploadedfile['type'][$key],
                );

                $movefile = wp_handle_upload($file, $upload_overrides);    
                if ( $movefile && !isset($movefile['error']) ) { 
                    $attachment_id = wp_insert_attachment($attachment, $movefile['file'], 0);

                    if(@is_array(getimagesize($movefile['file']))){
                        $src = $movefile['url'];
                    } else {
                        $src =  get_home_url() . '/wp-includes/images/media/default.png';
                    }

                    $attachment[] = array(
                        'guid' => $movefile['url'],
                        'post_mime_type' => $movefile['type'],
                        'post_title' => preg_replace( '/\.[^.]+$/', '', basename($movefile['file']) ),
                        'post_content' => '',
                        'post_status' => 'inherit',
                        'post_author' => 0,
                        'key' => $key,
                        'attachment_id' => $attachment_id,
                        'size' => $uploadedfile['size'][$key],
                        'src' => $src
                    );
                }
            }

            wp_send_json_success($attachment);
        } else {
            wp_send_json_error(false);
        }
        
        die();
    }
}

new ACF_Woo_Ajax();