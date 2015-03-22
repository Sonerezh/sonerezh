<?php

App::uses('AppModel', 'Model');

/**
 * Class Playlist
 *
 * @property PlaylistMembership $PlaylistMembership
 */
class Playlist extends AppModel {
    public $hasMany = array(
        'PlaylistMembership' => array(
            'dependent' => true
        )
    );

    public function beforeSave($options = array()) {
        $this->data[$this->alias]['user_id'] = AuthComponent::user('id');
        return true;
    }
}