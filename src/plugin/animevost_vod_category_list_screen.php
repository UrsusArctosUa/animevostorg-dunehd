<?php

require_once 'simple_html_dom.php';

class AnimevostVodCategoryListScreen extends AbstractPreloadedRegularScreen {

    const ID = 'vod_category_list';

    public static function get_media_url_str($param_name) {
        $arr['screen_id']  = self::ID;
        $arr['param_name'] = $param_name;
        return MediaURL::encode($arr);
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
        if (!isset($this->list[$media_url->param_name])) {
            $this->fetch_list($media_url->param_name);
        }

        $list = $this->list[$media_url->param_name];

        $items = array();

        foreach ($list as $item) {
            $items[] = array
                (
                PluginRegularFolderItem::media_url        => AnimevostVodListScreen::get_media_url_str('/' . $media_url->param_name . '/%s', $item['id']),
                PluginRegularFolderItem::caption          => $item['caption'],
                PluginRegularFolderItem::view_item_params => array
                (
//                    ViewItemParams::icon_path               => $year->get_icon_path(),
//                    ViewItemParams::item_detailed_icon_path => $year->get_icon_path()
                )
            );
        }

        return $items;
    }

    private function fetch_list($name) {
//        hd_print('AnimevostVodCategoryListScreen::fetch_list $name: ' . var_export($name, true));
        $rawHtml = HD::http_get_document(AnimevostConfig::VOD_CATEGORIES_LIST_URL_FORMAT, array(
                    CURLOPT_USERAGENT => AnimevostConfig::USERAGENT));
        if (is_null($rawHtml)) {
            throw new Exception('Can not fetch categories list');
        }
        $html = str_get_html($rawHtml);
//        hd_print('AnimevostVodCategoryListScreen::fetch_list $rawHtml: ' . $rawHtml);

        $list = array();

        foreach ($html->find('ul#topnav a[href$=/' . $name . '/]', 0)->parent()->find('span a') as $element) {
            $item            = array();
            $link            = explode('/', $element->href);
            end($link);
            $item['id']      = prev($link);
            $item['caption'] = $element->plaintext;
            $list[]          = $item;
        }

        $this->list[$name] = $list;
    }

    protected function do_get_folder_views(&$plugin_cookies) {
        return AnimevostConfig::GET_VOD_CATEGORY_LIST_FOLDER_VIEWS();
    }

}
