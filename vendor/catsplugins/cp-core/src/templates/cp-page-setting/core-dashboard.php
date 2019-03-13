<?php

$data = wp_remote_get("https://catsplugins.com/data/core-dashboard.json?v");

if (is_wp_error($data)) return '';

if (!isset($data['body'])) return;
$data = json_decode($data['body'], true);

$contentTabHeader = [];
$tabCurrent = isset($_GET['tab']) ? $_GET['tab'] : 'plugins';

$titleCurrent = '';

foreach ($data as $key => $tab) {
    $target = $class = $url = '';

    if (isset($tab['external_link'])) {
        $url = $tab['external_link'];
        $target = "_blank";
    } else if (isset($tab['internal_link'])) {
        $url = $tab['internal_link'];
    } else {
        $url = add_query_arg([
            "tab" => sanitize_title($tab['name'])
        ]);

        if ($tabCurrent == sanitize_title($tab['name'])) {
            $class = "current";

            $titleCurrent = $tab['title_page'];
        }
    }
    $contentTabHeader[] = "<li class=\"\"><a target='{$target}' class='{$class}' href=\"{$url}\">{$tab['title_page']}</a></li>";
}


?>
<div class="wrap wrap-catsplugin">
    <h1 class="wp-heading-inline"><?php _e($titleCurrent, 'kingcomposer'); ?></h1>

    <div class="wp-filter">
        <ul class="filter-links">
            <?php echo implode('', $contentTabHeader); ?>
        </ul>
    </div>


    <div id="the-list">
        <?php foreach ($data as $key => $tabContent) : ?>

            <?php if (sanitize_title($tabContent['name']) != $tabCurrent) continue; ?>

            <?php if (isset($tabContent['content'])) echo "<div class=\"catsplugin-wysiwyg cp-box\">" . $tabContent['content'] . "</div>" ?>

            <?php if (isset($tabContent['items'])) : ?>
                <?php foreach ($tabContent['items'] as $k => $plugin) : ?>
                    <div class="plugin-card plugin-card-akismet">
                        <div class="plugin-card-top">
                            <div class="name column-name">
                                <h3>
                                    <a target="_blank" href="<?php echo $plugin['url'] ?>"
                                       class="">
                                        <?php echo $plugin['name'] ?>
                                        <img src="<?php echo $plugin['image'] ?>"
                                             class="plugin-icon" alt="">
                                    </a>
                                </h3>
                            </div>
                            <div class="action-links">
                                <ul class="plugin-action-buttons">
                                    <li><a target="_blank" class="install-now button"
                                           href="<?php echo $plugin['url'] ?>"
                                        >Buy now </a></li>
                                    <li>
                                        <b style="color:red"><?php echo $plugin['price'] ?></b>
                                    </li>
                                </ul>
                            </div>
                            <div class="desc column-description">
                                <p><?php echo $plugin['desc'] ?></p>
                                <p class="authors"><cite>By <a href="https://catsplugins.com">Catsplugin</a></cite></p>
                            </div>
                        </div>

                        <div class="plugin-card-bottom">

                            <div class="column-updated">
                                <strong>Last Updated:</strong>
                                <?php
                                $lastUpdate = DateTime::createFromFormat('d/m/Y', $plugin['last_update']);
                                echo human_time_diff($lastUpdate->getTimestamp());
                                ?>
                            </div>

                            <div class="column-downloaded">
                                <?php
                                if ($plugin['number_active'] >= 1000000) {
                                    $active_installs_text = _x('1+ Million', 'Active plugin installations');
                                } elseif (0 == $plugin['number_active']) {
                                    $active_installs_text = _x('Less Than 10', 'Active plugin installations');
                                } else {
                                    $active_installs_text = number_format_i18n($plugin['number_active']) . '+';
                                }
                                printf(__('%s Active Installations'), $active_installs_text);
                                ?>
                            </div>
                            <div class="column-compatibility">
                        <span class="compatibility-compatible">
                            <strong>Compatible</strong> with WordPress version <?php echo $plugin['compatible'] ?>
                        </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>