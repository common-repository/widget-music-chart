jQuery(document).ready(function($) {
    var needUpdate = window.widget_music_chart_need_update || [];
    var ajaxUrl = window.widgetMusicChartData.ajaxurl;

    needUpdate.forEach(function(id) {
        $.ajax({
            url: ajaxUrl,
            data: {
                action: 'update_music_chart_cache',
                id: id,
            }
        });
    });
});