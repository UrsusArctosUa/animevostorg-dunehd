<?php

require_once 'lib/vod/vod_search_screen.php';

class AnimevostVodSearchScreen extends VodSearchScreen {

    private function do_get_control_defs(&$plugin_cookies) {
        $defs = array();

        $this->add_text_field($defs, 'pattern', 'Название:', isset($plugin_cookies->vod_search_pattern)
                            ? $plugin_cookies->vod_search_pattern : '', false, false, false, false, 500);
        $this->add_button($defs, 'submit', null, 'Поиск', 100);
        return $defs;
    }

    public function get_control_defs(MediaURL $media_url, &$plugin_cookies) {
        $this->vod->folder_entered($media_url, $plugin_cookies);

        return $this->do_get_control_defs($plugin_cookies);
    }

    public function handle_user_input(&$user_input, &$plugin_cookies) {
        hd_print('Vod search: handle_user_input:');
        foreach ($user_input as $key => $value)
            hd_print("  $key => $value");

        $plugin_cookies->vod_search_pattern = $user_input->pattern;

        if ($user_input->control_id == 'submit') {
            $defs = $this->do_get_control_defs(&$plugin_cookies);
            return ActionFactory::reset_controls($defs, ActionFactory::open_folder(
                                    AnimevostVodSearchListScreen::get_media_url_str($plugin_cookies->vod_search_pattern)
                                    , 'Found video'));
        }
        return null;
    }

}

///////////////////////////////////////////////////////////////////////////
?>
