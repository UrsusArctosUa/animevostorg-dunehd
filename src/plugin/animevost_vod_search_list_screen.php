<?php

require_once 'lib/vod/vod_list_screen.php';
require_once 'simple_html_dom.php';

class AnimevostVodSearchListScreen extends VodListScreen {

    const ID = 'vod_search_list';

    public static function get_media_url_str($pattern) {
        $arr['screen_id'] = self::ID;
        $arr['pattern']   = $pattern;
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
        $rawHtml   = HD::http_get_document(AnimevostConfig::VOD_SEARCH_URL_FORMAT, array(
                    CURLOPT_POST       => 1,
                    CURLOPT_POSTFIELDS => http_build_query($post = array(
                        'do'           => 'search',
                        'subaction'    => 'search',
                        'search_start' => $this->get_page_for_index($from_ndx, 10),
                        'full_search'  => 0,
                        'result_from'  => $from_ndx,
                        'story'        => $media_url->pattern
                    )),
                    CURLOPT_USERAGENT  => AnimevostConfig::USERAGENT));
        if (is_null($rawHtml)) {
            throw new Exception('Can not fetch movie list');
        }
        hd_print('AnimevostVodCategoryListScreen::fetch_list $rawHtml: ' . $rawHtml);
        hd_print('AnimevostVodCategoryListScreen::fetch_list $post: ' . var_export($post, true));
        $html        = str_get_html($rawHtml);
        $total_pages = 0;
        foreach ($html->find('td.block_4 a') as $element) {
            $total_pages = max($total_pages, $element->plaintext);
        }
        $movies = array();
        foreach ($html->find('div#dle-content div.shortstory') as $element) {
            $link       = explode('/', $element->find('div.shortstoryHead a', 0)->href);
            $id         = end($link);
            $name       = $element->find('div.shortstoryHead a', 0)->plaintext;
            $poster_url = $element->find('img.imgRadius', 0)->src;
            $movies[]   = new ShortMovie(
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
