<?php

App::uses('AppModel', 'Model');

/**
 * Class Song
 *
 * @property PlaylistMembership $PlaylistMembership
 */
class Song extends AppModel {
    public $hasMany = array('PlaylistMembership');
}