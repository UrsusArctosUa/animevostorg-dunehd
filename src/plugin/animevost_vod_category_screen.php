<?php

require_once 'lib/vod/vod_list_screen.php';
require_once 'simple_html_dom.php';

class AnimevostVodCategoryScreen extends VodListScreen {

    const ID = 'vod_category';

    public static function get_media_url_str($cat_id) {
        $arr['screen_id']   = self::ID;
        $arr['category_id'] = $cat_id;
        return MediaURL::encode($arr);
    }

    ///////////////////////////////////////////////////////////////////////

    public function __construct(Vod $vod) {
        parent::__construct($vod);
    }

    ///////////////////////////////////////////////////////////////////////

    private function get_page_for_index($index, $page_size) {
        return intval($index / $page_size) + 1;
    }

    protected function get_short_movie_range(MediaURL $media_url, $from_ndx,
                                             &$plugin_cookies) {

        $page_size   = 10;
        $options     = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" . // check function.stream-context-create on php.net
                "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );
        $context     = stream_context_create($options);
        $rawHtml     = file_get_contents($url         = sprintf(AnimevostConfig::MOVIE_LIST_URL_FORMAT, $this->get_page_for_index($from_ndx, 10)), null, $context);
        hd_print('AnimevostVodListScreen::get_short_movie_range $url: ' . $url);
//        $rawHtml    = HD::http_get_document(sprintf(AnimevostConfig::MOVIE_LIST_URL_FORMAT,
//                $this->get_page_for_index($from_ndx, 10)));
        $html        = str_get_html($rawHtml);
        $total_pages = 0;
        foreach ($html->find('td.block_4 a') as $element) {
            $total_pages = max($total_pages, $element->plaintext);
        }

        if (is_null($html))
            throw new Exception('Can not fetch movie list');


        $movies = array();
        foreach ($html->find('div#dle-content div.shortstory') as $element) {
            $link       = explode('/', $element->find('div.shortstoryHead a', 0)->href);
            $id         = end($link);
            hd_print('AnimevostVodListScreen::get_short_movie_range $id: ' . $id);
            $name       = $element->find('div.shortstoryHead a', 0)->plaintext;
            $poster_url = $element->find('img.imgRadius', 0)->src;
            $movies[]   = new ShortMovie(
                    $id, $name, $poster_url);
        }
        return new ShortMovieRange($from_ndx, $total_pages * $page_size, $movies);
    }

}
