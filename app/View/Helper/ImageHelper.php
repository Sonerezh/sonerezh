<?php

App::uses('AppHelper', 'View/Helper');

class ImageHelper extends AppHelper{

    public $helpers = array('Html');

    public function resizedPath($path, $width = 0, $height = 0){
        $extension = '.'.pathinfo($path, PATHINFO_EXTENSION);
        if($extension != '.gif'){
            $path = str_replace($extension, "_".$width."x".$height.$extension, $path);
        }
        return $path;
    }

    public function lazyload($path, $options = array()){
        $image = $this->Html->image($path, $options);
        return str_replace('src="', 'src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" onload="lzld(this)" data-src="', $image);
    }

    public function avatar($auth, $size = null){
        $image = $auth['avatar'];
        if(empty($image)){
            $image = 'https://secure.gravatar.com/avatar/'.md5($auth['email']).'.png?s='.$size;
        }else{
            $image = $this->resizedPath("avatars".DS.$auth['avatar'], $size, $size);
        }
        return $image;
    }

}