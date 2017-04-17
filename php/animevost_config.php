<?php

// $demo_config_m3u_file_url = dirname(__FILE__) . '/iptv.utf8.m3u';

class AnimevostConfig {

    const VOD_MOVIE_PAGE_SUPPORTED = true;
    const VOD_FAVORITES_SUPPORTED  = true;
    const FAV_MOVIES_CATEGORY_ICON_PATH = 'plugin_file://icons/fav.png';
    const MOVIE_LIST_URL_FORMAT         = 'http://animevost.org/page/%s/';
    const MOVIE_INFO_URL_FORMAT         = 'http://animevost.org/tip/tv/%s';

    ///////////////////////////////////////////////////////////////////////
    // Folder views.
    public static function GET_VOD_MOVIE_LIST_FOLDER_VIEWS() {
        return array(
            array
                (
                PluginRegularFolderView::async_icon_loading => true,
                PluginRegularFolderView::view_params => array
                    (
                    ViewParams::num_cols                  => 5,
                    ViewParams::num_rows                  => 2,
//                    ViewParams::icon_selection_box_width  => 150,
//                    ViewParams::icon_selection_box_height => 222,
                    ViewParams::paint_details             => true,
                    ViewParams::zoom_detailed_icon        => false,
                ),
                PluginRegularFolderView::base_view_item_params => array
                    (
                    ViewItemParams::item_padding_top      => 0,
                    ViewItemParams::item_padding_bottom   => 0,
                    ViewItemParams::icon_valign           => VALIGN_CENTER,
                    ViewItemParams::item_paint_caption    => false,
                    ViewItemParams::icon_scale_factor     => 1.0,
                    ViewItemParams::icon_sel_scale_factor => 1.2,
                ),
                PluginRegularFolderView::not_loaded_view_item_params => array
                    (
                    ViewItemParams::icon_path               => 'plugin_file://icons/mov_unset.png',
                    ViewItemParams::item_detailed_icon_path => 'missing://',
                ),
        ));
    }

    public static function GET_VOD_CATEGORY_LIST_FOLDER_VIEWS() {
        return array(
            array
                (
                PluginRegularFolderView::async_icon_loading => false,
                PluginRegularFolderView::view_params => array
                    (
                    ViewParams::num_cols                        => 5,
                    ViewParams::num_rows                        => 4,
                    ViewParams::paint_details                   => false,
                    ViewParams::paint_sandwich                  => true,
                    ViewParams::sandwich_base                   => 'gui_skin://special_icons/sandwich_base.aai',
                    ViewParams::sandwich_mask                   => 'cut_icon://{name=sandwich_mask}',
                    ViewParams::sandwich_cover                  => 'cut_icon://{name=sandwich_cover}',
                    ViewParams::sandwich_width                  => 245,
                    ViewParams::sandwich_height                 => 140,
                    ViewParams::sandwich_icon_upscale_enabled   => true,
                    ViewParams::sandwich_icon_keep_aspect_ratio => false,
                ),
                PluginRegularFolderView::base_view_item_params => array
                    (
                    ViewItemParams::item_paint_icon       => true,
                    ViewItemParams::item_layout           => HALIGN_CENTER,
                    ViewItemParams::icon_valign           => VALIGN_CENTER,
                    ViewItemParams::item_paint_caption    => false,
                    ViewItemParams::icon_scale_factor     => 1.0,
                    ViewItemParams::icon_sel_scale_factor => 1.2,
                ),
                PluginRegularFolderView::not_loaded_view_item_params => array(),
            ),
            array
                (
                PluginRegularFolderView::async_icon_loading => false,
                PluginRegularFolderView::view_params => array
                    (
                    ViewParams::num_cols                        => 4,
                    ViewParams::num_rows                        => 3,
                    ViewParams::paint_details                   => false,
                    ViewParams::paint_sandwich                  => true,
                    ViewParams::sandwich_base                   => 'gui_skin://special_icons/sandwich_base.aai',
                    ViewParams::sandwich_mask                   => 'cut_icon://{name=sandwich_mask}',
                    ViewParams::sandwich_cover                  => 'cut_icon://{name=sandwich_cover}',
                    ViewParams::sandwich_width                  => 245,
                    ViewParams::sandwich_height                 => 140,
                    ViewParams::sandwich_icon_upscale_enabled   => true,
                    ViewParams::sandwich_icon_keep_aspect_ratio => false,
                ),
                PluginRegularFolderView::base_view_item_params => array
                    (
                    ViewItemParams::item_paint_icon       => true,
                    ViewItemParams::item_layout           => HALIGN_CENTER,
                    ViewItemParams::icon_valign           => VALIGN_CENTER,
                    ViewItemParams::item_paint_caption    => false,
                    ViewItemParams::icon_scale_factor     => 1.25,
                    ViewItemParams::icon_sel_scale_factor => 1.5,
                ),
                PluginRegularFolderView::not_loaded_view_item_params => array(),
            ),
        );
    }

}
