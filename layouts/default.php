<?php
defined('WPINC') or die('error');

$data = MusicChartHelper::getChartData($this, $instance);

echo $args['before_widget'];
if (!empty($instance['title'])) {
    echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
}

$num_item = (int) $instance['number_item'];
$items = array();

foreach ($data['items'] as $key => $value) {
    if ($key >= $num_item) {
        break;
    }

    $value->isLong = $key + 1 > 99 ? true : false;

    switch ($value->trend) {
        case 'rising':
            $value->trend_icon = '<img src="' . plugins_url('widget-music-chart/assets/images/up.svg') . '" />';
            break;

        case 'falling':
            $value->trend_icon = '<img src="' . plugins_url('widget-music-chart/assets/images/down.svg') . '" />';
            break;

        case 'steady':
            $value->trend_icon = '<img src="' . plugins_url('widget-music-chart/assets/images/right.svg') . '" />';
            break;

        case 'reenter':
            $value->trend_icon = __('ReEnter', 'widget-music-chart');
            break;

        default:
            $value->trend_icon = __('New', 'widget-music-chart');
            break;
    }

    $promo = $value->last - $value->rank;

    if ($value->trend === 'new' || $value->trend === 'reenter' || $promo === 0) {
        $value->promo = '-';
    } else if ($promo > 0) {
        $value->promo = "+$promo";
    } else {
        $value->promo = $promo;
    }

    if (!$value->image) {
        $value->image = plugins_url('widget-music-chart/assets/images/song-icon.jpg');
    }

    $items[] = $value;
}

?>
<div class="widget-<?php echo $this->id ?> ff-music-items">
    <?php foreach ($items as $key => $item): ?>
        <div class="ff-music-item">
            <div class="ff-music-item__rank <?php echo $item->isLong ? 'ff-music-item__rank--long' : '' ?>">
                <div class="rank__number"><?php echo $key + 1 ?></div>
                <div class="trend__icon color--<?php echo $item->trend ?>"><?php echo $item->trend_icon ?></div>
            </div>
            <div class="ff-music-item__detail">
                <div class="ff-music-item__title">
                    <?php echo $item->title ?>
                </div>
                <div class="ff-music-item__subtitle">
                    <?php echo $item->subtitle ?>
                </div>
                <div class="ff-music-item__promo color--<?php echo $item->trend ?>">
                    <span><?php echo $item->promo ?></span>
                </div>
                <div class="ff-music-item__meta">
                    <span title="<?php _e('Last Week', 'widget-music-chart') ?>">
                        <?php echo $item->last ?> <span class="ff-music-item__meta-label"><?php _e('Last', 'widget-music-chart') ?></span>
                    </span> |
                    <span title="<?php _e('Peak', 'widget-music-chart') ?>">
                        <?php echo $item->peak ?> <span class="ff-music-item__meta-label"><?php _e('Peak', 'widget-music-chart') ?></span>
                    </span> |
                    <span title="<?php _e('Duration', 'widget-music-chart') ?>">
                        <?php echo $item->duration ?> 
                        <span class="ff-music-item__meta-label">
                            <?php echo $item->duration < 2 ? _e('Week', 'widget-music-chart') : _e('Weeks', 'widget-music-chart') ?>
                        </span>
                    </span>
                </div>
            </div>
            <div class="ff-music-item__image">
                <img src="<?php echo $item->image ?>" alt="<?php echo $item->title ?>">
            </div>
        </div>
    <?php endforeach ?>
</div>
<?php if (!empty($data['needUpdate'])): ?>
<script>
    window.widget_music_chart_need_update = window.widget_music_chart_need_update || [];
    window.widget_music_chart_need_update.push(<?php echo $this->number ?>);
</script>
<?php endif ?>
<?php
echo $args['after_widget'];
