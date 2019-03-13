# Cài đặt bằng composer
```json
{
  "...": "....",
  "require": {
      "php": ">=7.1",
      "catsplugins/cp-core": "@dev"
  },
  "repositories": [
      {
          "type": "git",
          "url":  "https://gitlab.com/catsplugins/cp-core.git"
      }
  ]
}
```

## Cách sử dụng

```php

<?php 
//Khởi tạo class với tên và thư mục gốc của plugin
$cpCoreDemo = new CpCore('plugin_name', [
    'plugin_path' => 'path_root_plugin'
]);

// KHai báo trang dashboard của plugin đuợc dựng từ file json
$cpCoreDemo->createPageSetting([
    'file' => CPLS_ROOT_PATH . '/config/admin_setting.json'
]);

// Khai báo các tham số của merlin
$cpCoreDemo->merlin([
    'merlin_url' => 'cpls-merlin',
    'edd_theme_slug' => 'cpls-merlin',
], [
    'admin-menu' => esc_html(__('Cpls Import content')),
    'license%s' => "Enter your license key and Email"
],array(
    array(
        'import_file_name'     => 'Demo Import 1',
        'import_file_url'      => CP_LIVE_PLUGIN_URL . '/data-sample/demo.wordpress.2018-10-30.xml'
    )
));

// Khai báo các tham số của TGM để cài 1 số plugin
$plugins = array(
    array(
        'name'      => 'Page Builder: KingComposer – Free Drag and Drop page builder by King-Theme',
        'slug'      => 'kingcomposer',
        'required'  => true,
        'force_activation' => true
    ),
);
$cpCoreDemo->tgm($plugins);

?>

```

### Cấu trúc file JSON của page settings
```json
{
  "page_title" : "Live Search - Ajax Search & Filter Builder",
  "menu_title" :"Live Search",
  "capability" :"manage_options",
  "menu_slug" :"cpls-dashboard",
  "icon_url" :"",
  "prefix_options": "cpls_",
  "tabs": {
    "Wellcome": {
        // Tab với type là content sẽ show nội dung, đuợc tải từ  3 loại content như bên duới      
        "settings":{
          "type": "content",
          "content_html": "",
          "content_remote": "https://catsplugins.com/data/dashboard-catsplugin.html?v1",
          // Tên function sẽ trả về nội dung của tab này
          "content_callback_function":""
        }
    },
    "General": {
      "settings": {
        // Cho phép hiển thị nút submit cuối tab để submit form
        "submit_button": true
      },
      "fields": {
        "test-key": {
          "type": "text",
          "label" : "Test key setting",
          // Các hàm sẽ lọc content, hàm này 1 tham số và return về string
          "filters": ["strip_tags", "trim"],
          "default": ""
        },

        "email_lic": {
          "type": "text",
          "label" : "Email license"
        }
      }
    },
    "Tools": {
      "settings": {
        "submit_button": false
      },
      "fields": {
        "page_import": {
          "type": "button-link",
          "label": "Import data",
          "text_button": "Run import",
          "type_button": "success",
          // Link đuợc lấy từ apply_filters('cpls-merlin')
          "link_filter": "cpls-merlin",
          "desc": "Import data sample, Awesome content."
        }
      }
    }
  }
}
```


### Ghi chú

- Khi sử dụng merlin sẽ có 1 filter trả về link của trang setting đó, filter với tên là slug của page merlin trong config merlin theo từng plugin



### Cần bổ sung
- Tạo page dashboard từ Array
- Tự động load 1 thư mục
- Module quản lý action - filter riêng để quản lý các action - filter dùng trong plugin