<?php

App::uses('AppController', 'Controller');

/**
 * Class PlaylistMembershipsController
 * Manage adding and deleting tracks in playlists. This controller binds SongsController and PlaylistController.
 *
 * @property PlaylistMembership $PlaylistMembership
 */
class PlaylistMembershipsController extends AppController {

    /**
     * This function adds songs into your favorites playlists.
     * All the information is passed through a POST request. To add multiple songs at the same time you can use a list
     * of song IDs separated by dashes : $this->request->data['Song']['id'] = '1-2-3-4-5'
     */
    public function add() {
        if ($this->request->is('post')) {
            // Verify that Playlist.id is correct
            if (empty($this->request->data['Playlist']['id']) && empty($this->request->data['Playlist']['title'])) {
                $this->Session->setFlash(__('You must specify a valid playlist'), 'flash_error');
                return $this->redirect($this->referer());
            }

            $playlist_length = 0;
            // Verify that Playlist.id exists
            if (isset($this->request->data['Playlist']['id']) && !empty($this->request->data['Playlist']['id'])) {
                $playlist = $this->PlaylistMembership->Playlist->exists($this->request->data['Playlist']['id']);

                if (empty($playlist)) {
                    $this->Session->setFlash(__('You must specify a valid playlist'), 'flash_error');
                    return $this->redirect($this->referer());
                }

                // Get playlist length to add the song at the end of the playlist
                $playlist_length = $this->PlaylistMembership->find('count', array(
                    'conditions' => array('PlaylistMembership.playlist_id' => $this->request->data['Playlist']['id'])
                ));

                // Unset Playlist.title if Playlist.id is set to avoid erase Playlist.title
                unset($this->request->data['Playlist']['title']);
            }

            $data = array('Playlist' => $this->request->data['Playlist']);
            //Simple song id
            if (isset($this->request->data["song"])) {
                $data['PlaylistMembership'][] = array(
                    'song_id' => $this->request->data['song'],
                    'sort' => $playlist_length+1
                );
            } else if(isset($this->request->data['band'])) { //It's a band !
                $conditions = array('Song.band' => $this->request->data['band']);
                $order = array('Song.band', 'Song.album', 'Song.disc+0', 'Song.track_number');

                if (isset($this->request->data['album'])) { //It's an album !
                    $conditions['Song.album'] = $this->request->data['album'];
                    $order = array('Song.album', 'Song.disc+0', 'Song.track_number');
                }

                $songsId = $this->PlaylistMembership->Song->find('list', array('conditions' => $conditions, 'order' => $order));
                foreach ($songsId as $key => $songId) {
                    $data['PlaylistMembership'][] = array(
                        'song_id' => $key,
                        'sort' => ++$playlist_length
                    );
                }
            }

            // Save data
            if ($this->PlaylistMembership->Playlist->saveAll($data, array('deep' => true))) {
                $this->Session->setFlash(__('Song successfully added to playlist'), 'flash_success');
            } else {
                $this->Session->setFlash(__('Unable to add the song'), 'flash_error');
            }
            //$this->redirect($this->referer());
            $this->render(false);
        } else {
            throw new MethodNotAllowedException();
        }
    }

    /**
     * This function removes songs from a playlist.
     *
     * @param int $id The ID of the song to be removed.
     * @todo Add the ability to remove multiple songs at once.
     */
    public function remove($id) {
        $this->PlaylistMembership->read(null, $id);
        if ($this->PlaylistMembership->delete($id)) {
            $this->Session->setFlash(__('Song successfully removed from playlist'), 'flash_success');
            $this->redirect($this->referer());
        }
    }
}