<?php

require_once 'lib/vod/vod_list_screen.php';
require_once 'simple_html_dom.php';

class AnimevostVodListScreen extends VodListScreen {

    const ID = 'vod_list';

    public static function get_media_url_str($param_string = '', $param_value = null) {
        $arr['screen_id']   = self::ID;
        $arr['param_string']   = $param_string;
        $arr['param_value']   = $param_value;
        return MediaURL::encode($arr);
    }

    public function __construct(Vod $vod) {
        parent::__construct(self::ID, $vod);
    }

    private function get_page_for_index($index, $page_size) {
        return intval($index / $page_size) + 1;
    }

    protected function get_short_movie_range(MediaURL $media_url, $from_ndx,
                                             &$plugin_cookies) {
        $page_size = 10;
        $param = sprintf($media_url->param_string, $media_url->param_value);
        $rawHtml    = HD::http_get_document(sprintf(AnimevostConfig::VOD_MOVIE_LIST_URL_FORMAT,
                $param, $this->get_page_for_index($from_ndx, 10)), array(
                    CURLOPT_USERAGENT => AnimevostConfig::USERAGENT));
        $html       = str_get_html($rawHtml);
        $total_pages = 0;
        foreach ($html->find('td.block_4 a') as $element) {
            $total_pages = max($total_pages, $element->plaintext);
        }

        if (is_null($html))
            throw new Exception('Can not fetch movie list');


        $movies = array();
        foreach ($html->find('div#dle-content div.shortstory') as $element) {
            $link = explode('/', $element->find('div.shortstoryHead a', 0)->href);
            $id = end($link);
            $name = $element->find('div.shortstoryHead a', 0)->plaintext;
            $poster_url = $element->find('img.imgRadius', 0)->src;
            $movies[] = new ShortMovie(
                    $id, $name, $poster_url);
        }
        if($total_pages == 0){
            $total = count($movies);
        }
        else{
            $total = $total_pages * $page_size;
        }
        return new ShortMovieRange($from_ndx, $total, $movies);
    }

}
