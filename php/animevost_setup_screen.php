<?php

///////////////////////////////////////////////////////////////////////////

require_once 'lib/abstract_controls_screen.php';

///////////////////////////////////////////////////////////////////////////

class AnimevostSetupScreen extends AbstractControlsScreen {

    const ID = 'setup';

    ///////////////////////////////////////////////////////////////////////

    public function __construct() {
        parent::__construct(self::ID);
    }

    public function do_get_control_defs(&$plugin_cookies) {
        $defs = array();

        $show_tv  = isset($plugin_cookies->show_tv) ?
                $plugin_cookies->show_tv : 'yes';
        $show_vod = isset($plugin_cookies->show_vod) ?
                $plugin_cookies->show_vod : 'yes';

        $show_ops        = array();
        $show_ops['yes'] = 'Yes';
        $show_ops['no']  = 'No';

        $this->add_combobox($defs, 'show_tv', 'Show TV in main screen:', $show_tv, $show_ops, 0, true);

        $this->add_combobox($defs, 'show_vod', 'Show VOD in main screen:', $show_vod, $show_ops, 0, true);

        return $defs;
    }

    public function get_control_defs(MediaURL $media_url, &$plugin_cookies) {
        return $this->do_get_control_defs($plugin_cookies);
    }

    public function handle_user_input(&$user_input, &$plugin_cookies) {
        hd_print('Setup: handle_user_input:');
        foreach ($user_input as $key => $value)
            hd_print("  $key => $value");

        if ($user_input->action_type === 'confirm') {
            $control_id = $user_input->control_id;
            $new_value  = $user_input->{$control_id};
            hd_print("Setup: changing $control_id value to $new_value");

            if ($control_id === 'show_tv')
                $plugin_cookies->show_tv  = $new_value;
            else if ($control_id === 'show_vod')
                $plugin_cookies->show_vod = $new_value;
        }

        return ActionFactory::reset_controls(
                        $this->do_get_control_defs($plugin_cookies));
    }

}
