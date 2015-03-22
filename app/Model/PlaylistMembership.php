<?php

App::uses('AppModel', 'Model');

/**
 * Class PlaylistMembership
 *
 * @property Song $Song
 * @property Playlist $Playlist
 */
class PlaylistMembership extends AppModel {
    public $belongsTo = array('Song', 'Playlist');

    public function afterDelete() {
        if (!empty($this->data)) {
            $this->updateAll(
                array('PlaylistMembership.sort' => '`PlaylistMembership`.`sort` - 1'),
                array(
                    'PlaylistMembership.playlist_id'    => $this->data['PlaylistMembership']['playlist_id'],
                    'PlaylistMembership.sort >'         => $this->data['PlaylistMembership']['sort']
                )
            );
        }
    }


}