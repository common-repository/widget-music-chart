<?php
/*
Plugin Name: Widget Music Chart
Plugin URI: https://wordpress.org/plugins/widget-music-chart/
Description: Allows you to show charts from billboard.com or officialcharts.com
Version: 1.0
Author: Mr. Meo
Author URI: https://github.com/trananhmanh89/
*/

defined('WPINC') or die('error');

class WidgetMusicChart_Widget extends WP_Widget
{
	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="widget-wrap">',
		'after_widget'  => '</div></div>'
	);

	protected $layoutPaths = array();

	function __construct()
	{
		$this->layoutPaths[] = get_template_directory() . '/widget-music-chart/';
		$this->layoutPaths[] = __DIR__ . '/layouts/';

		wp_enqueue_style('widget-music-chart-css', plugins_url('assets/widget-music-chart.css', __FILE__));
		parent::__construct(
			'widget-music-chart',
			'Music Chart Widget'
		);
	}

	protected function getListLayout()
	{
		$layouts = array();
		foreach ($this->layoutPaths as $path) {
			if (!is_dir($path)) {
				continue;
			}

			$items = list_files($path, 1);
			foreach ($items as $item) {
				$info = pathinfo($item);
				if (isset($info['extension']) 
					&& $info['extension'] === 'php' 
					&& !in_array($info['filename'], $layouts)) {

					$layouts[] = $info['filename'];
				}
			}
		}

		sort($layouts);

		return $layouts;
	}

	protected function getLayoutPath($layout)
	{
		foreach ($this->layoutPaths as $path) {
			$file = $path . $layout . '.php';
			if (is_file($file)) {
				return $file;
			}
		}

		return __DIR__ . '/layouts/default.php';
	}

	public function widget($args, $instance)
	{
		require_once __DIR__ . '/helper.php';
		$layout = !empty($instance['layout']) ? $instance['layout'] : 'default';

		include $this->getLayoutPath($layout);
	}

	public function form($instance)
	{
		wp_enqueue_script('jquery');
		wp_enqueue_script('widget-music-chart-admin', plugins_url('assets/widget-music-chart-admin.js', __FILE__));
		include __DIR__ . '/admin/form.php';
	}

	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		$instance['source'] = (!empty($new_instance['source'])) ? strip_tags($new_instance['source']) : 'billboard';
		$instance['billboard_chart'] = (!empty($new_instance['billboard_chart'])) ? strip_tags($new_instance['billboard_chart']) : 'billboard_hot_100';
		$instance['official_chart'] = (!empty($new_instance['official_chart'])) ? strip_tags($new_instance['official_chart']) : 'uk_single_top_100';
		$instance['update_time'] = (!empty($new_instance['update_time'])) ? $new_instance['update_time'] : '3600';
		$instance['number_item'] = (!empty($new_instance['number_item'])) ? $new_instance['number_item'] : '10';
		$instance['layout'] = (!empty($new_instance['layout'])) ? $new_instance['layout'] : 'default';

		return $instance;
	}
}

add_action('widgets_init', function () {
	register_widget('WidgetMusicChart_Widget');
});

function widget_music_chart_update_cache() {
	require_once __DIR__ . '/helper.php';
	MusicChartHelper::updateChartCache();
	die('done');
}

add_action('wp_ajax_nopriv_update_music_chart_cache', 'widget_music_chart_update_cache');
add_action('wp_ajax_update_music_chart_cache', 'widget_music_chart_update_cache');

add_action('wp_footer', function() {
	wp_enqueue_script( "widget_music_chart_js", plugins_url('/assets/widget-music-chart.js', __FILE__), array('jquery') );
	wp_localize_script( 'widget_music_chart_js', 'widgetMusicChartData', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
});