<?php
defined('WPINC') or die('error');

$layouts = $this->getListLayout();
$currentLayout = !empty($instance['layout']) ? $instance['layout'] : 'default';
$title = !empty($instance['title']) ? $instance['title'] : '';
$source = !empty($instance['source']) ? $instance['source'] : 'billboard';
$billboard_chart = !empty($instance['billboard_chart']) ? $instance['billboard_chart'] : 'billboard_hot_100';
$official_chart = !empty($instance['official_chart']) ? $instance['official_chart'] : 'uk_single_top_100';
$update_time = !empty($instance['update_time']) ? $instance['update_time'] : '3600';
$number_item = !empty($instance['number_item']) ? $instance['number_item'] : '10';
?>
<div class="music-chart-widget-admin-form">
    <p>
        <label for="">Like my work?</label>
        <br>
        <br>
        <a href='https://ko-fi.com/I3I71FSC5' target='_blank'><img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi1.png?v=2' border='0' alt='Buy Me a Coffee at ko-fi.com' /></a>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html__('Title:', 'widget-music-chart'); ?></label>
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
            type="text" 
            value="<?php echo esc_attr($title); ?>">
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('source')); ?>"><?php echo esc_html__('Source:', 'widget-music-chart'); ?></label>
        <select 
            class="widefat chart-source"
            name="<?php echo esc_attr($this->get_field_name('source')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('source')); ?>"
            onchange="typeof toggleChartSource === 'function' && toggleChartSource(jQuery(this).parents('.music-chart-widget-admin-form'))">
            <option value="billboard" <?php selected($source, 'billboard') ?> >billboard.com</option>
            <option value="officialcharts" <?php selected($source, 'officialcharts') ?>>officialcharts.com</option>
        </select>
    </p>
    <p class="billboard-chart-list">
        <label for="<?php echo esc_attr($this->get_field_id('billboard_chart')); ?>"><?php echo esc_html__('Billboard Chart:', 'widget-music-chart'); ?></label>
        <select 
            class="widefat"
            name="<?php echo esc_attr($this->get_field_name('billboard_chart')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('billboard_chart')); ?>">
            <option value="billboard_hot_100" <?php selected($billboard_chart, 'billboard_hot_100') ?>>Hot 100</option>
            <option value="billboard_200" <?php selected($billboard_chart, 'billboard_200') ?>>Billboard 200</option>
            <option value="billboard_artist_100" <?php selected($billboard_chart, 'billboard_artist_100') ?>>Artist 100</option>
            <option value="billboard_top_pop_songs" <?php selected($billboard_chart, 'billboard_top_pop_songs') ?>>Top Pop Songs</option>
            <option value="billboard_top_country_songs" <?php selected($billboard_chart, 'billboard_top_country_songs') ?>>Top Country Songs</option>
            <option value="billboard_top_country_albums" <?php selected($billboard_chart, 'billboard_top_country_albums') ?>>Top Country Albums</option>
        </select>
    </p>
    <p class="official-chart-list">
        <label for="<?php echo esc_attr($this->get_field_id('official_chart')); ?>"><?php echo esc_html__('Official Chart UK:', 'widget-music-chart'); ?></label>
        <select 
            class="widefat"
            name="<?php echo esc_attr($this->get_field_name('official_chart')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('official_chart')); ?>">
            <option value="uk_single_top_100" <?php selected($official_chart, 'uk_single_top_100') ?>>Uk Single Top 100</option>
            <option value="uk_album_top_100" <?php selected($official_chart, 'uk_album_top_100') ?>>Uk Album Top 100</option>
        </select>
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('update_time')); ?>"><?php echo'Update Time (second):'; ?></label>
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('update_time')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('update_time')); ?>" 
            type="number" 
            value="<?php echo esc_attr($update_time); ?>">
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('number_item')); ?>"><?php echo'Number Item:'; ?></label>
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('number_item')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('number_item')); ?>" 
            type="number" 
            value="<?php echo esc_attr($number_item); ?>">
    </p>
    <p>
        <label for="<?php echo esc_attr($this->get_field_id('layout')); ?>"><?php echo esc_html__('Layout:', 'widget-music-chart'); ?></label>
        <select 
            class="widefat"
            name="<?php echo esc_attr($this->get_field_name('layout')); ?>" 
            id="<?php echo esc_attr($this->get_field_id('layout')); ?>">
            <?php foreach ($layouts as $layout): ?>
                <option value="<?php echo $layout ?>" <?php selected($currentLayout, $layout) ?>>
                    <?php echo $layout ?>
                </option>
            <?php endforeach ?>
        </select>
    </p>
</div>
<script>
    typeof initChartSource === 'function' && initChartSource();
</script>