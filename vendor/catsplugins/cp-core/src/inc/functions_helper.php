<?php


if (!function_exists('_cp')) {
    function _cp($text)
    {
        _e($text, 'CatsPlugins');
    }

}
if (!function_exists('__cp')) {

    function __cp($text)
    {
        return __($text, 'CatsPlugins');
    }
}

if (!function_exists('cpFormatDateTime')) {
    function cpFormatDateTime(int $time)
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        return date("{$timeFormat} {$dateFormat}", $time);
    }
}
if (!function_exists('cpAdminPagination')) {
    function cpAdminPagination(int $totalItem, int $current)
    {

        $args = array(
            'base' => '%_%',
            'format' => '?paged=%#%',
            'total' => $totalItem,
            'current' => $current,
            'show_all' => false,
            'end_size' => 1,
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => __('« Previous'),
            'next_text' => __('Next »'),
            'type' => 'plain',
            'add_args' => false,
            'add_fragment' => '',
            'before_page_number' => '',
            'after_page_number' => ''
        );
        return "<span class=\"displaying-num\">{$totalItem} items</span> " . paginate_links($args);
    }
}