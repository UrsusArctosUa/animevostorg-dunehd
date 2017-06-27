<?php

require_once 'lib/vod/movie.php';

class AnimevostMovie extends Movie
{
    public function remove_series_data(){
        $this->series_list = array();
    }

    public function set_name($name){
        $this->name = $this->to_string($name);
    }

    public function set_name_original($name){
        $this->name_original = $this->to_string($name);
    }
}
