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
                $this->Flash->error(__('You must specify a valid playlist'));
                return $this->redirect($this->referer());
            }

            $playlist_length = 0;
            // Verify that Playlist.id exists
            if (isset($this->request->data['Playlist']['id']) && !empty($this->request->data['Playlist']['id'])) {
                $playlist = $this->PlaylistMembership->Playlist->exists($this->request->data['Playlist']['id']);

                if (empty($playlist)) {
                    $this->Flash->error(__('You must specify a valid playlist'));
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

            } elseif (isset($this->request->data['band'])) { // It's a band!
                $conditions = array('Song.band' => $this->request->data['band']);
                $order = 'band';

                if (isset($this->request->data['album'])) { // It's an album!
                    $conditions['Song.album'] = $this->request->data['album'];
                    $order = 'disc';
                }

                $songs = $this->PlaylistMembership->Song->find('all', array(
                    'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.track_number', 'Song.disc'),
                    'conditions'    => $conditions
                ));

                $this->SortComponent = $this->Components->load('Sort');

                if ($order == 'band') {
                    $songs = $this->SortComponent->sortByBand($songs);
                } elseif ($order == 'disc') {
                    $songs = $this->SortComponent->sortByDisc($songs);
                }

                foreach ($songs as $song) {
                    $data['PlaylistMembership'][] = array(
                        'song_id' => $song['Song']['id'],
                        'sort' => ++$playlist_length
                    );
                }
            }

            // Save data
            if ($this->PlaylistMembership->Playlist->saveAll($data, array('deep' => true))) {
                $this->Flash->success(__('Song successfully added to playlist'));
            } else {
                $this->Flash->error(__('Unable to add the song'));
            }

            $this->PlaylistMembership->Playlist->recursive = 0;
            $playlists = $this->PlaylistMembership->Playlist->find('list', array(
                'fields'        => array('Playlist.id', 'Playlist.title'),
                'conditions'    => array('user_id' => AuthComponent::user('id'))
            ));
            $this->set('playlists', json_encode($playlists, true));
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
            $this->Flash->success(__('Song successfully removed from playlist'));
            $this->redirect($this->referer());
        }
    }
}