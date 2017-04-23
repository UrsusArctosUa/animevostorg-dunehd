<?php

require_once 'simple_html_dom.php';

class AnimevostVodGenreListScreen extends AbstractPreloadedRegularScreen {

    const ID = 'vod_genre_list';

    public static function get_media_url_str() {
        return MediaURL::encode(array('screen_id' => self::ID));
    }

    public function __construct() {
        parent::__construct(self::ID);
    }

    private $list;

    public function get_action_map(MediaURL $media_url, &$plugin_cookies) {
        return array(
            GUI_EVENT_KEY_ENTER => ActionFactory::open_folder(),
        );
    }

    public function get_all_folder_items(MediaURL $media_url, &$plugin_cookies) {
        if (is_null($this->list)) {
            $this->fetch_list();
        }

        $list = $this->list;

        $items = array();

        foreach ($list as $item) {
            $items[] = array
                (
                PluginRegularFolderItem::media_url        => AnimevostVodListScreen::get_media_url_str('/zhanr/%s', $item['id']),
                PluginRegularFolderItem::caption          => $item['caption'],
                PluginRegularFolderItem::view_item_params => array
                (
//                    ViewItemParams::icon_path               => $genre->get_icon_path(),
//                    ViewItemParams::item_detailed_icon_path => $genre->get_icon_path()
                )
            );
        }

        return $items;
    }

    private function fetch_list() {
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" . // check function.stream-context-create on php.net
                "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );
        $context = stream_context_create($options);
        $rawHtml = file_get_contents(AnimevostConfig::VOD_GENRE_LIST_URL_FORMAT, null, $context);
//        $rawHtml    = HD::http_get_document(sprintf(AnimevostConfig::VOD_GENRE_LIST_URL_FORMAT,
//                $param, $this->get_page_for_index($from_ndx, 10)));
        $html    = str_get_html($rawHtml);

        if (is_null($html))
            throw new Exception('Can not fetch genre list');

        $list = array();

        foreach ($html->find('ul#topnav a[href$=/zhanr/]', 0)->parent()->find('span a') as $element) {
            $item            = array();
            $link            = explode('/', $element->href);
            end($link);
            $item['id']      = prev($link);
            $item['caption'] = $element->plaintext;
            $list[]          = $item;
        }

        $this->list = $list;
    }

    protected function do_get_folder_views(&$plugin_cookies) {
        return AnimevostConfig::GET_VOD_CATEGORY_LIST_FOLDER_VIEWS();
    }

}
