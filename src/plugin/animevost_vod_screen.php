<?php

class AnimevostVodScreen extends AbstractPreloadedRegularScreen {

    const ID = 'vod';

    public static function get_media_url_str() {
        return MediaURL::encode(array('screen_id' => self::ID));
    }

    public function __construct() {
        parent::__construct(self::ID);
    }

    public function get_action_map(MediaURL $media_url, &$plugin_cookies) {
        return array(
            GUI_EVENT_KEY_ENTER => ActionFactory::open_folder(),
        );
    }

    public function get_all_folder_items(MediaURL $media_url, &$plugin_cookies) {
        $items = array();

        if (AnimevostConfig::VOD_FAVORITES_SUPPORTED) {
            $items[] = array
                (
                PluginRegularFolderItem::media_url        => VodFavoritesScreen::get_media_url_str(),
                PluginRegularFolderItem::caption          => T::t('vod_favorites_label'),
                PluginRegularFolderItem::view_item_params => array
                    (
                    ViewItemParams::icon_path               => 'missing://',
//                    ViewItemParams::icon_path               => 'plugin_file://icons/fav.png',
//                    ViewItemParams::item_detailed_icon_path => 'plugin_file://icons/fav.png',
                )
            );
        }

        $items[] = array
            (
            PluginRegularFolderItem::media_url        => AnimevostVodListScreen::get_media_url_str(),
            PluginRegularFolderItem::caption          => T::t('vod_latest_label'),
            PluginRegularFolderItem::view_item_params => array
                (
                ViewItemParams::icon_path               => 'missing://',
//                ViewItemParams::icon_path               => 'plugin_file://icons/latest.png',
//                ViewItemParams::item_detailed_icon_path => 'plugin_file://icons/latest.png'
            )
        );
        $items[] = array
            (
            PluginRegularFolderItem::media_url        => AnimevostVodGenreListScreen::get_media_url_str(),
            PluginRegularFolderItem::caption          => T::t('vod_genre_list_label'),
            PluginRegularFolderItem::view_item_params => array
                (
                ViewItemParams::icon_path               => 'missing://',
//                ViewItemParams::icon_path               => 'plugin_file://icons/latest.png',
//                ViewItemParams::item_detailed_icon_path => 'plugin_file://icons/latest.png'
            )
        );
        $items[] = array
            (
            PluginRegularFolderItem::media_url        => AnimevostVodYearListScreen::get_media_url_str(),
            PluginRegularFolderItem::caption          => T::t('vod_year_list_label'),
            PluginRegularFolderItem::view_item_params => array
                (
                ViewItemParams::icon_path               => 'missing://',
//                ViewItemParams::icon_path               => 'plugin_file://icons/latest.png',
//                ViewItemParams::item_detailed_icon_path => 'plugin_file://icons/latest.png'
            )
        );
        return $items;
    }

    protected function do_get_folder_views(&$plugin_cookies) {
        return AnimevostConfig::GET_VOD_CATEGORY_LIST_FOLDER_VIEWS();
    }

}
