<?php

require_once 'lib/vod/abstract_vod.php';
require_once 'lib/vod/movie.php';
require_once 'animevost_vod_movie.php';
require_once 'simple_html_dom.php';

class AnimevostVod extends AbstractVod {

    public function __construct() {
        parent::__construct(
                AnimevostConfig::VOD_FAVORITES_SUPPORTED, AnimevostConfig::VOD_MOVIE_PAGE_SUPPORTED, true);
    }

    public function get_cached_movie($movie_id) {
//        hd_print('AbstractVod::get_cached_movhd_printie $movie_id: ' . var_export($movie_id, true));
//        hd_print('AbstractVod::get_cached_movie $this->movie_by_id: ' . var_export($this->movie_by_id, true));
        $movie = parent::get_cached_movie($movie_id);
        if (empty($movie)) {
            return null;
        }
        $expl_id = explode('-', $movie_id);
        reset($expl_id);
        $movie_real_id = current($expl_id);
//        hd_print('AnimevostVod::get_cached_movie $movie_real_id '.$movie_real_id);
        $series        = json_decode(HD::http_get_document(AnimevostConfig::VOD_MOVIE_SERIES_URL_FORMAT, array(
                    CURLOPT_USERAGENT      => AnimevostConfig::USERAGENT,
                    CURLOPT_POST           => 1,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0,
                    CURLOPT_POSTFIELDS     => 'titleid=' . $movie_real_id)), true);
        $urls          = array();
        foreach ($series as $sery) {
            $expl_name = explode(' ', $sery['name']);
            reset($expl_name);
            $id        = current($expl_name);
            $def       = isset($sery['std']) ? 'std' : 'hd';
            $urls[$id] = array(
                'id'   => $movie_real_id . '_' . $id . '_' . $def,
                'name' => $sery['name'],
                'url'  => $sery[$def]
            );
        }
        ksort($urls);
//        hd_print(var_export($urls, true));
        $movie->remove_series_data();
        foreach ($urls as $url) {
            $movie->add_series_data($url['id'], $url['name'], $url['url'], true);
        }
//        $expl_name = explode('[', $movie->name);
//        reset($expl_name);
//        $movie->set_name(current($expl_name) . '[1' . '-' . count($urls) . ']');
        return $movie;
    }

    public function try_load_movie($movie_id, &$plugin_cookies) {

        $rawHtml = HD::http_get_document(sprintf(AnimevostConfig::VOD_MOVIE_INFO_URL_FORMAT, $movie_id), array(
                    CURLOPT_USERAGENT => AnimevostConfig::USERAGENT));

        if (is_null($rawHtml))
            throw new Exception('Can not fetch movie info');
        $html  = str_get_html($rawHtml);
        $movie = new AnimevostMovie($movie_id);

        $element = $html->find('div.shortstory', 0);
        $head    = explode('/', str_replace(']', '', str_replace('[', '/', $element->find('div.shortstoryHead h1', 0)->plaintext)));

        $movie->set_data(
                trim($head[0]) . ' [' . trim($head[2]) . ']', trim($head[1]) . ' [' . trim($head[2]) . ']', preg_replace('/\s+/', ' ', $element->find('span[itemprop=description]', 0)->plaintext), $element->find('img.imgRadius', 0)->src, null, null, null, null, null, null, null, null, null, null, null
        );
        // ad functionality
//        if (count($movie->series_list) == 1) {
//            $movie->set_advert_urls(
//                    $xml->preplay_url, $xml->postplay_url);
//            hd_print("XXX DEBUG: preplay=" . strval($xml->preplay_url) .
//                    ", postplay=" . strval($xml->postplay_url));
//        }
//        hd_print('AnimevostVod::try_load_movie $movie: '. var_export($movie, true));

        $this->set_cached_movie($movie);
    }

    protected function load_favorites(&$plugin_cookies) {
        $fav_movie_ids = $this->get_fav_movie_ids_from_cookies($plugin_cookies);

        foreach ($fav_movie_ids as $movie_id) {
            if ($this->has_cached_short_movie($movie_id))
                continue;

            $this->ensure_movie_loaded($movie_id, $plugin_cookies);
        }

        $this->set_fav_movie_ids($fav_movie_ids);

        hd_print('The ' . count($fav_movie_ids) . ' favorite movies loaded.');
    }

    protected function do_save_favorite_movies(&$fav_movie_ids, &$plugin_cookies) {
        $this->set_fav_movie_ids_to_cookies($plugin_cookies, $fav_movie_ids);
    }

    public function get_fav_movie_ids_from_cookies(&$plugin_cookies) {
        if (!isset($plugin_cookies->{'favorite_movies'}))
            return array();

        $arr = preg_split('/,/', $plugin_cookies->{'favorite_movies'});

        $ids = array();
        foreach ($arr as $id) {
            if (preg_match('/\S/', $id))
                $ids[] = $id;
        }
        return $ids;
    }

    public function set_fav_movie_ids_to_cookies(&$plugin_cookies, &$ids) {
        $plugin_cookies->{'favorite_movies'} = join(',', $ids);
    }

    public function get_vod_list_folder_views(&$plugin_cookies) {
        return AnimevostConfig::GET_VOD_MOVIE_LIST_FOLDER_VIEWS();
    }

}
