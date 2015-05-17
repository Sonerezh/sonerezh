<?php

App::uses('AppModel', 'Model');
class Rootpath extends AppModel {

    public $belongsTo = array('Setting');
    public $validate = array(
        'rootpath'	=> array(
            'pathExists'	=> array(
                'rule'			=> 'pathExists',
                'message'		=> 'Invalid root path'
            ),
            'isReadable'	=> array(
                'rule'			=> 'isReadable',
                'message'		=> 'Root path folder is not readable'
            ),
        )
    );

    public function pathExists($options = array()) {
        if (!file_exists($this->data[$this->alias]['rootpath'])) {
            return false;
        }
        return true;
    }

    public function isReadable($options = array()) {
        if (!is_readable($this->data[$this->alias]['rootpath'])) {
            return false;
        }

        if (preg_match('/^\s/', $this->data[$this->alias]['rootpath']) || preg_match('/\s$/', $this->data[$this->alias]['rootpath'])) {
            return false;
        }
        return true;
    }

}