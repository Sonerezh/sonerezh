<?php

App::uses('AppModel', 'Model');

/**
 * Class Song
 *
 * @property PlaylistMembership $PlaylistMembership
 */
class Song extends AppModel {
    public $hasMany = array('PlaylistMembership');

    public function beforeSave($options = array()) {
        // Verify the fields only on creation
        if (!isset($this->data[$this->alias]['id'])) {
            if (empty($this->data[$this->alias]['album'])) {
                $this->data[$this->alias]['album'] = 'Unknown Album';
            }

            if (empty($this->data[$this->alias]['artist'])) {
                $this->data[$this->alias]['artist'] = 'Unknown Artist';
            }

            if (empty($this->data[$this->alias]['band'])) {
                $this->data[$this->alias]['band'] = $this->data[$this->alias]['artist'];
            }
        }
    }
}