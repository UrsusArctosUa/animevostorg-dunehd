<?php

///////////////////////////////////////////////////////////////////////////

require_once 'lib/default_dune_plugin.php';
require_once 'lib/utils.php';

require_once 'lib/vod/vod_list_screen.php';
require_once 'lib/vod/vod_movie_screen.php';
require_once 'lib/vod/vod_series_list_screen.php';
require_once 'lib/vod/vod_favorites_screen.php';

require_once 'animevost_config.php';

require_once 'animevost_vod.php';
require_once 'animevost_vod_category_list_screen.php';
require_once 'animevost_vod_latest_screen.php';
require_once 'animevost_setup_screen.php';

///////////////////////////////////////////////////////////////////////////

class AnimevostPlugin extends DefaultDunePlugin {

    public function __construct() {
        $this->vod = new AnimevostVod();

        $this->add_screen(new VodFavoritesScreen($this->vod));
        $this->add_screen(new AnimevostVodCategoryListScreen());
        $this->add_screen(new AnimevostVodLatestScreen($this->vod));
        $this->add_screen(new VodMovieScreen($this->vod));
        $this->add_screen(new VodSeriesListScreen($this->vod));
        $this->add_screen(new AnimevostSetupScreen());
    }

}
