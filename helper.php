<?php 
defined('WPINC') or die('error');

use Rct567\DomQuery\DomQuery;

class MusicChartHelper
{
    public static function getChartData($widget, $settings)
    {
        $data = array();
        $cachePath = __DIR__ . '/cache';
        $source = $settings['source'];
        if ($source === 'billboard') {
            $type = $settings['billboard_chart'];
        } else {
            $type = $settings['official_chart'];
        }

        $strId = $source . '-' . $type . '-' . $widget->number;

        $updateTime = +$settings['update_time'];
        $updateTimeFile = $cachePath . '/lastUpdate-' . $strId . '.txt';
        $lastUpdate = file_exists($updateTimeFile) ? +file_get_contents($updateTimeFile) : 0;
        $now = time();

        $cacheFile = $cachePath . '/cache-' . $strId . '.txt';
        
        if ($lastUpdate + $updateTime < $now && file_exists($cacheFile)) {
            $data['needUpdate'] = true;
        }

        if (file_exists($cacheFile)) {
            $data['items'] = @json_decode(@file_get_contents($cacheFile));
        } else {
            $data['items'] = self::setChartCache($cacheFile, $updateTimeFile, $type);
        }

        return $data;
    }

    protected static function setChartCache($cacheFile, $updateTimeFile, $type)
    {
        require_once __DIR__ . '/vendor/DomQuery/CssToXpath.php';
        require_once __DIR__ . '/vendor/DomQuery/DomQueryNodes.php';
        require_once __DIR__ . '/vendor/DomQuery/DomQuery.php';

        $cachePath = __DIR__ . '/cache';

        if (!is_dir($cachePath)) {
            wp_mkdir_p($cachePath);
        }

        file_put_contents($updateTimeFile, time());

        switch ($type) {
            case 'billboard_hot_100':
                $data = self::getBillboardChartByData('https://www.billboard.com/charts/hot-100');
                break;

            case 'billboard_200':
                $data = self::getBillboardChartByData('https://www.billboard.com/charts/billboard-200');
                break;

            case 'billboard_artist_100';
                $data = self::getBillboardChartByDom('https://www.billboard.com/charts/artist-100');
                break;

            case 'billboard_top_pop_songs';
                $data = self::getBillboardChartByDom('https://www.billboard.com/charts/pop-songs');
                break;

            case 'billboard_top_country_songs';
                $data = self::getBillboardChartByDom('https://www.billboard.com/charts/country-songs');
                break;

            case 'billboard_top_country_albums';
                $data = self::getBillboardChartByDom('https://www.billboard.com/charts/country-albums');
                break;

            case 'uk_single_top_100':
                $data = self::getUkChart('https://www.officialcharts.com/charts/singles-chart/');
                break;

            case 'uk_album_top_100':
                $data = self::getUkChart('https://www.officialcharts.com/charts/albums-chart/');
                break;
            
            default:
                $data = array();
                break;
        }

        file_put_contents($cacheFile, json_encode($data));
        file_put_contents($updateTimeFile, time());

        return $data;
    }

    public static function updateChartCache()
    {
        $cachePath = __DIR__ . '/cache';

        if (!is_dir($cachePath)) {
            wp_mkdir_p($cachePath);
        }

        $id = filter_input(INPUT_GET, 'id');
        if (!$id) {
            die('missing id');
        }

        $widgets = get_option('widget_widget-music-chart');
        if (empty($widgets[$id])) {
            die('widget not found');
        }

        $settings = $widgets[$id];

        $source = $settings['source'];
        if ($source === 'billboard') {
            $type = $settings['billboard_chart'];
        } else {
            $type = $settings['official_chart'];
        }

        $strId = $source . '-' . $type . '-' . $id;

        $updateTime = +$settings['update_time'];
        $updateTimeFile = $cachePath . '/lastUpdate-' . $strId . '.txt';
        $lastUpdate = file_exists($updateTimeFile) ? +file_get_contents($updateTimeFile) : 0;
        $now = time();

        $cacheFile = $cachePath . '/cache-' . $strId . '.txt';

        if ($lastUpdate + $updateTime < $now) {
            self::setChartCache($cacheFile, $updateTimeFile, $type);
        }
    }

    protected static function getUkChart($url)
    {
        $html = self::crawl($url);
        
        try {
            $dom = DomQuery::create($html);
            $dom->find('.headings')->remove();
            $dom->find('.mobile-actions')->remove();
            $dom->find('.actions-view')->remove();
            $dom->find('tr > td > .adspace')->parent()->parent()->remove();

            $list = $dom->find('.chart-positions > tr');
            $items = array();

            foreach ($list as $elm) {
                $item = new stdClass;
                $td = $elm->find('td');
                $item->rank = (int) trim(DomQuery::create($td->get(0))->text());
                $item->last = (int) trim(DomQuery::create($td->get(1))->text());
                $item->peak = (int) trim(DomQuery::create($td->get(3))->text());
                $item->duration = (int) trim(DomQuery::create($td->get(4))->text());
                $item->trend = self::parseTrend($item);

                $track = $elm->find('.track');
                $item->title = trim($track->find('.title')->text());
                $item->subtitle = trim($track->find('.artist')->text());

                $cover = $track->find('.cover img');
                $img = self::getImage('https://www.officialcharts.com', $cover->attr('src'));
                $item->image = str_replace('img/small', 'img/medium', $img);
                
                $items[] = $item;
            }

            return $items;
        } catch (Exception $e) {
            return array();
        }
    }

    protected static function getImage($host, $src)
    {
        if (preg_match('/^(http|https):\/\/.*/', $src)) {
            return $src;
        } else {
            return $host . $src;
        }
    }

    protected static function getBillboardChartByDom($url)
    {
        $html = self::crawl($url);

        try {
            $dom = DomQuery::create($html);
            
            $list = $dom->find('.chart-list .chart-list-item');
            $items = array();

            foreach ($list as $elm) {
                $item = new stdClass;
                $item->title = trim($elm->data('title'));
                $item->subtitle = trim($elm->data('artist'));
                $item->rank = (int) trim($elm->data('rank'));

                $miniStats = $elm->find('.chart-list-item__ministats  > .chart-list-item__ministats-cell');
                $item->last = (int) trim($miniStats->first()->text());
                $item->duration = (int) trim($miniStats->last()->text());
                $item->peak = (int) trim(DomQuery::create($miniStats->get(1))->text());

                $item->trend = self::parseTrend($item);

                $img = $elm->find('.chart-list-item__image-wrapper > img');
                $src = $img->attr('src');
                if (preg_match('/bb-placeholder-new\.jpg/', $src)) {
                    $item->image = '';
                } else {
                    $srcset = $img->data('srcset');
                    $set = explode(',', $srcset);
                    $last = array_pop($set);
                    $trimmed = trim($last);
                    $exploded = explode(' ', $trimmed);
                    $item->image = @$exploded[0];
                }

                $items[] = $item;
            }

            return $items;
        } catch (Exception $e) {
            return array();
        }
    }

    protected static function getBillboardChartByData($url)
    {
        $html = self::crawl($url);

        try {
            $dom = DomQuery::create($html);
            $elm = $dom->find('#charts');
            $data = $elm->attr('data-charts');
            $data = @json_decode($data);

            if (!$data || !is_array($data)) {
                return array();
            }

            $chartData = array_map(function($item) {
                $result = new stdClass;
                $result->title = $item->title;
                $result->subtitle = $item->artist_name;
                $result->rank = (int) $item->rank;
                $result->duration = (int) $item->history->weeks_on_chart;
                $result->peak = (int) $item->history->peak_rank;
                $result->last = (int) $item->history->last_week;
                $result->trend = self::parseTrend($result);

                if (isset($item->title_images->sizes->{'ye-landing-sm'})) {
                    $result->image = 'https://charts-static.billboard.com' . $item->title_images->sizes->{'ye-landing-sm'}->Name;
                } else {
                    $result->image = '';
                }

                return $result;
            }, $data);

            return $chartData;
        } catch (Exception $e) {
            return array();
        }
    }

    protected static function parseTrend($item)
    {
        $trend = $item->rank - $item->last;

        if ($item->duration == 1) {
            return 'new';
        } else if ($item->duration > 1 && !$item->last) {
            return 'reenter';
        } else if ($trend === 0) {
            return 'steady';
        } else if ($trend < 0 ) {
            return 'rising';
        } else {
            return 'falling';
        }
    }

    protected static function crawl($url)
    {
        $res = wp_remote_get($url);

        if ($res['response']['code'] !== 200) {
            die("Could not get data from $url");
        }

        return $res['body'];
    }
}