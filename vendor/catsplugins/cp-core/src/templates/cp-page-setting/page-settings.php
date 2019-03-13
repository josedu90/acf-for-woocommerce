<?php
\CastPlugin\CpCore::loadVue();

$actionForm = 'cp-' . $config['menu_slug'];
$nonceField = 'cp-field-' . $config['menu_slug'];

$modelVue = [];

if (isset($_POST['submit']) && $_POST['submit'] == $actionForm) {
    if (check_admin_referer($actionForm, $nonceField)) {

        foreach ($config['tabs'] as $tabName => $tabContent) {
            if (isset($tabContent['fields'])) {
                foreach ($tabContent['fields'] as $keyName => $fieldData) {
                    $keyName = (isset($config['prefix_options']) ? $config['prefix_options'] : '') . $keyName;
                    $keyName = \CastPlugin\CpPageSetting::sanitizeKeyName($keyName);
                    $value = \CastPlugin\CpPageSetting::filterOption($keyName, $fieldData);
                    (update_option($keyName, $value));
                }
            }
        }
    }


}


?>
<div class="wrap">
    <h1><?php echo $config['page_title'] ?><?php echo __(' Settings') ?></h1>

    <div id="appCpSettings">
        <el-form method="post" ref="form" :model="form" label-width="200px">


            <el-tabs type="border-card" @tab-click="hanldeTabClick">
                <?php foreach ($config['tabs'] as $tabName => $tabContent) : ?>
                    <?php
                        $settings = isset($tabContent['settings']) ? $tabContent['settings'] : [];

                        $event = [];
                        if (isset($settings['type']) && $settings['type'] == 'link') {
                            $link = isset($settings['link']) ? $settings['link'] : '';
                        }
                    ?>

                    <el-tab-pane label="<?php echo $tabName ?>" <?php echo implode(' ', $event); ?> >
                        <?php

                        if (isset($tabContent['settings'])) {


                            if (isset($settings['type']) && $settings['type'] == 'content') {
                                echo "<div class='cpContent'>";
                                if (isset($settings['content_remote']) && !empty($settings['content_remote'])) {
                                    $data = wp_remote_get($settings['content_remote']);
                                    if (isset($data['body'])) {
                                        echo $data['body'];
                                    }
                                }

                                if (isset($settings['content_callback_function']) && function_exists($settings['content_callback_function'])) {
                                    call_user_func($settings['content_callback_function']);
                                }


                                if (isset($settings['content_html']) && !empty($settings['content_html'])) {
                                    echo $settings['content_html'];
                                }

                                echo "</div>";
                            }
                        }
                        if (isset($tabContent['fields'])) :
                            foreach ($tabContent['fields'] as $keyName => $fieldData) :
                                $keyName = (isset($config['prefix_options']) ? $config['prefix_options'] : '') . $keyName;
                                $keyName = \CastPlugin\CpPageSetting::sanitizeKeyName($keyName);

                                $vueElement = new \CastPlugin\CpVueElement($keyName, $fieldData);
                                $vueElement->render();

                                if (isset($fieldData['options'])) {
                                    $default = [];
                                } else {
                                    $default = '';
                                }

                                $modelVue[$keyName] = (get_option($keyName, (isset($fieldData['default']) ? $fieldData['default'] : $default)));
                            endforeach;
                            ?>


                            <?php if (isset($tabContent['settings']['submit_button']) && $tabContent['settings']['submit_button'] == true) : ?>
                                <el-form-item>
                                    <el-button native-type="submit" name="submit" value="<?php echo $actionForm ?>"
                                               type="primary"><?php _e('Save change', 'catsplugin') ?></el-button>
                                </el-form-item>
                            <?php endif; ?>


                            <?php
                        endif;

                        ?>
                    </el-tab-pane>
                <?php endforeach; ?>
            </el-tabs>


            <?php wp_nonce_field($actionForm, $nonceField); ?>
        </el-form>
    </div>
</div>


<script>
    var cpVueApp = new Vue({
        el: '#appCpSettings',
        data: function () {
            return {
                form: <?php echo json_encode($modelVue) ?>,
                data_setting: <?php echo json_encode($config['tabs']) ?>
            }
        },

        methods: {
            redirect: function(link) {
                location.href=link;
            },

            hanldeTabClick: function(tab, event){
                var tabName = tab.label;
                var that = this;

                if (typeof that.data_setting[tabName] !== 'undefined') {
                    var settings = that.data_setting[tabName]['settings'];
                    if (settings.type === 'link') {
                        settings.link = settings.link.replace('__link_admin__', '<?php echo get_admin_url() ?>')
                        window.location.href = settings.link;
                    }
                }
            }

        }
    });
</script>
