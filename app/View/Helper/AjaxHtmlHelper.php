<?php

App::uses('HtmlHelper', 'View/Helper');

class AjaxHtmlHelper extends HtmlHelper{

    public function link($title, $url = null, $options = array(), $confirmMessage = false) {
        if($confirmMessage){
            $options['data-confirm'] = $confirmMessage;
            $confirmMessage = false;
        }
        return parent::link($title, $url, $options, $confirmMessage);
    }

}