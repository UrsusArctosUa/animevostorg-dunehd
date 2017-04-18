<?php

///////////////////////////////////////////////////////////////////////////

require_once 'lib/vod/abstract_vod.php';
require_once 'lib/vod/movie.php';
require_once 'simple_html_dom.php';

///////////////////////////////////////////////////////////////////////////

class AnimevostVod extends AbstractVod {

    public function __construct() {
        parent::__construct(
                AnimevostConfig::VOD_FAVORITES_SUPPORTED, AnimevostConfig::VOD_MOVIE_PAGE_SUPPORTED, true);
    }

    ///////////////////////////////////////////////////////////////////////

    /**
     * Disabling cache
     */
    public function ensure_movie_loaded($movie_id, &$plugin_cookies) {

        if (!isset($movie_id))
            throw new Exception('Movie ID is not set');

        $this->try_load_movie($movie_id, $plugin_cookies);
    }

    public function try_load_movie($movie_id, &$plugin_cookies) {

        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                "Cookie: foo=bar\r\n" . // check function.stream-context-create on php.net
                "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n" // i.e. An iPad
            )
        );
        $context = stream_context_create($options);
        $rawHtml = file_get_contents(sprintf(AnimevostConfig::MOVIE_INFO_URL_FORMAT, $movie_id), null, $context);
//        $rawHtml = HD::http_get_document(sprintf(AnimevostConfig::MOVIE_INFO_URL_FORMAT, $movie_id));
        if (is_null($rawHtml))
            throw new Exception('Can not fetch movie info');
        $html    = str_get_html($rawHtml);
        $movie = new Movie($movie_id);

        $element        = $html->find('div.shortstory', 0);
        $head           = explode('/', str_replace(']', '', str_replace('[', '/', $element->find('div.shortstoryHead h1', 0)->plaintext)));

        $movie->set_data(
                trim($head[0]) . ' [' . trim($head[2]) . ']', // caption
                trim($head[1]) . ' [' . trim($head[2]) . ']', // caption_original
                preg_replace('/\s+/', ' ', $element->find('span[itemprop=description]', 0)->plaintext), // description
                $element->find('img.imgRadius', 0)->src, // poster_url
                null, // length
                null, // year
                null, // director
                null, // scenario
                null, // actors
                null, // genres
                null, // rate_imdb
                null, // rate_kinopoisk
                null, // rate_mpaa
                null, // country
                null // budget
                );

        preg_match_all('|"([0-9]+ серия)":"([0-9]+)"|', $rawHtml, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $movie->add_series_data($match[2] . '_std', $match[1] . ' (std)', sprintf('http://mp4.aniland.org/%s.mp4', $match[2]), true);
            $movie->add_series_data($match[2] . '_hd', $match[1] . ' (hd)', sprintf('http://mp4.aniland.org/720/%s.mp4', $match[2]), true);
        }

        // ad functionality
//        if (count($movie->series_list) == 1) {
//            $movie->set_advert_urls(
//                    $xml->preplay_url, $xml->postplay_url);
//            hd_print("XXX DEBUG: preplay=" . strval($xml->preplay_url) .
//                    ", postplay=" . strval($xml->postplay_url));
//        }

        $this->set_cached_movie($movie);
    }

    ///////////////////////////////////////////////////////////////////////
    // Favorites.

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

    ///////////////////////////////////////////////////////////////////////

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

    ///////////////////////////////////////////////////////////////////////
    // Genres.

    /*
      protected function load_genres(&$plugin_cookies)
      {
      $doc = $this->session->api_vod_genres();

      $genres = array();
      foreach ($doc->genres as $genre)
      $genres[$genre->id] = $genre->name;

      return $genres;
      }

      public function get_genre_icon_url($genre_id)
      {
      return $this->session->get_icon('mov_genre_default.png');
      }

      public function get_genre_media_url_str($genre_id)
      {
      return AnimevostVodListScreen::get_media_url_str('genres', $genre_id);
      }
     */

    ///////////////////////////////////////////////////////////////////////
    // Search.

    /*
      public function get_search_media_url_str($pattern)
      {
      return AnimevostVodListScreen::get_media_url_str('search', $pattern);
      }
     */

    ///////////////////////////////////////////////////////////////////////
    // Folder views.

    public function get_vod_list_folder_views(&$plugin_cookies) {
        return AnimevostConfig::GET_VOD_MOVIE_LIST_FOLDER_VIEWS();
    }

}
