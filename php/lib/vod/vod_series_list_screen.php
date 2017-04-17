<?php
///////////////////////////////////////////////////////////////////////////

require_once 'lib/vod/vod.php';
require_once 'lib/abstract_preloaded_regular_screen.php';

class VodSeriesListScreen extends AbstractPreloadedRegularScreen
{
    const ID = 'vod_series';

    public static function get_media_url_str($movie_id)
    {
        return MediaURL::encode(
            array(
                'screen_id' => self::ID,
                'movie_id' => $movie_id));
    }

    ///////////////////////////////////////////////////////////////////////

    private $vod;

    public function __construct(Vod $vod)
    {
        $this->vod = $vod;

        parent::__construct(self::ID);
    }

    protected function do_get_folder_views(&$plugin_cookies)
    {
        return $this->vod->get_vod_series_folder_views($plugin_cookies);
    }

    ///////////////////////////////////////////////////////////////////////

    public function get_action_map(MediaURL $media_url, &$plugin_cookies)
    {
        return array
        (
            GUI_EVENT_KEY_ENTER => ActionFactory::vod_play(),
            GUI_EVENT_KEY_PLAY  => ActionFactory::vod_play(),
        );
    }

    ///////////////////////////////////////////////////////////////////////

    public function get_all_folder_items(MediaURL $media_url, &$plugin_cookies)
    {
        $this->vod->folder_entered($media_url, $plugin_cookies);

        $movie = $this->vod->get_loaded_movie($media_url->movie_id, $plugin_cookies);
        if ($movie === null)
        {
            // TODO: dialog?
            return array();
        }

        $items = array();

        foreach ($movie->series_list as $series)
        {
            $items[] = array
            (
                PluginRegularFolderItem::media_url =>
                    MediaURL::encode(
                        array
                        (
                            'screen_id' => self::ID,
                            'movie_id' => $movie->id,
                            'series_id'  => $series->id,
                        )),
                PluginRegularFolderItem::caption => $series->name,
                PluginRegularFolderItem::view_item_params => array
                (
                    ViewItemParams::icon_path => 'gui_skin://small_icons/movie.aai',
                ),
            );
        }

        return $items;
    }

    ///////////////////////////////////////////////////////////////////////

    public function get_archive(MediaURL $media_url, &$plugin_cookies)
    {
        return $this->vod->get_archive($media_url, $plugin_cookies);
    }
}

///////////////////////////////////////////////////////////////////////////
?>
