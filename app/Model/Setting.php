<?php

App::uses('AppModel', 'Model');

class Setting extends AppModel {
    public $hasMany = array('Rootpath');
    public $name = 'Setting';
    public $validate = array(
        'enable_auto_conv'	=> array(
            'boolean'			=> array(
                'rule'				=> array('boolean'),
                'message'			=> 'Something went wrong!'
            )
        ),
        'convert_to'	=> array(
            'inList'		=> array(
                'rule'			=> array('inList', array('mp3', 'ogg')),
                'message'		=> 'Wrong conversion destination!'
            ),
            'convConflicts'	=> array(
                'rule'			=> array('convConflicts'),
                'message'		=> "Wrong conversion destination! Make sure you are not trying to convert MP3 to MP3, or OGG to OGG."
            )
        ),
        'enable_mail_notification'	=> array(
            'boolean'			=> array(
                'rule'				=> array('boolean'),
                'message'			=> 'Something went wrong!'
            )
        )
    );

    public function beforeSave($options = array()) {
        // On place les fichiers à convertir dans ['convert_from']
        $this->data[$this->alias]['convert_from'] = '';
        if (isset($this->data[$this->alias]['from_mp3']) && $this->data[$this->alias]['from_mp3']) {
            $this->data[$this->alias]['convert_from'] .= 'mp3,';
        }
        if (isset($this->data[$this->alias]['from_ogg']) && $this->data[$this->alias]['from_ogg']) {
            $this->data[$this->alias]['convert_from'] .= 'ogg,';
        }
        if (isset($this->data[$this->alias]['from_flac']) && $this->data[$this->alias]['from_flac']) {
            $this->data[$this->alias]['convert_from'] .= 'flac,';
        }
        // On force la conversion des fichiers AAC
        $this->data[$this->alias]['convert_from'] .= 'aac';
        return true;
    }

    public function convConflicts($options = array()) {
        // Make sure Sonerezh will not try to convert MP3 to MP3 or OGG to OGG
        if (isset($this->data[$this->alias]['from_mp3'])) {
            if ($this->data[$this->alias]['from_mp3'] && $this->data[$this->alias]['convert_to'] == 'mp3') {
                return false;
            }
        }

        if (isset($this->data[$this->alias]['from_ogg'])) {
            if ($this->data[$this->alias]['from_ogg'] && $this->data[$this->alias]['convert_to'] == 'ogg') {
                return false;
            }
        }
        return true;
    }

}