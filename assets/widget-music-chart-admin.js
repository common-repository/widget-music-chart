(function($) {
    initChartSource = function() {
        $('.music-chart-widget-admin-form').each(function() {
            var $elm = $(this);

            if ($elm.hasClass('done')) {
                return;
            }

            $elm.addClass('done');
            
            toggleChartSource($elm);
        });
    }

    toggleChartSource = function($elm) {
        var $source = $elm.find('.chart-source');
        var $billboard = $elm.find('.billboard-chart-list');
        var $official = $elm.find('.official-chart-list');

        if ($source.val() === 'billboard') {
            $billboard.show();
            $official.hide();
        } else {
            $billboard.hide();
            $official.show();
        }
    }

    $(document).ready(function() {
        initChartSource();
    })

    window.initChartSource = initChartSource;
    window.toggleChartSource = toggleChartSource;
})(jQuery)
