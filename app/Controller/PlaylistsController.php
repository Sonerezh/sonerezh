<?php

App::uses('AppController', 'Controller');

/**
 * Class PlaylistsController
 * Manage adding, editing and deleting playlists.
 *
 * @property Playlist $Playlist
 */
class PlaylistsController extends AppController {

    /**
     * Retrieve the current user playlists, and songs of a given playlist before pass them to the view.
     *
     * @param int|null $id The playlist ID.
     */
    public function index($id = null) {

        /**
         * @var array Array of playlist songs.
         */
        $playlist = array();

        /**
         * @var string Name of playlist songs.
         */
        $playlistName = null;

        $playlistInfo = array();

        /**
         * @var array Array of user playlists.
         */
        $playlists = $this->Playlist->find('list', array(
            'fields' => array('id', 'title'),
            'conditions' => array('user_id' => AuthComponent::user('id'))
        ));

        // Find playlist content
        if (!empty($playlists)) {
            if ($id == null) {
                $id = key($playlists);
            }
            $playlistInfo = array('id' => $id, 'name' => $playlists[$id]);
            $this->Playlist->PlaylistMembership->contain('Song');
            $playlist = $this->Playlist->PlaylistMembership->find('all', array(
                'conditions' => array('PlaylistMembership.playlist_id' => $id),
                'order' => 'PlaylistMembership.sort'
            ));
        }

        $this->set(compact('playlists', 'playlist', 'playlistInfo'));
    }

    /**
     * Manage playlist creation. Each playlist is linked to the user that creates it.
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Playlist->create();

            if ($this->Playlist->save($this->request->data)) {
                $this->Flash->success(__('Playlist created: '), array(
                    'params' => array($this->request->data['Playlist']['title'])
                ));
            } else {
                $this->Flash->error(__('Unable to create the playlist: '), array(
                    'params' => array($this->request->data['Playlist']['title'])
                ));
            }

            $this->redirect(array('action' => 'index'));
        }
    }

    /**
     * Manage playlist edition.
     *
     * @param int $id The playlist to rename.
     */
    public function edit($id) {
        if (!$id) {
            throw new NotFoundException(__('Invalid playlist ID'));
        }

        $playlist = $this->Playlist->findById($id);

        if (!$playlist) {
            throw new NotFoundException(__('Invalid playlist ID'));
        }

        if ($this->request->is(array('post', 'put'))) {
            $this->Playlist->id = $id;

            if ($this->Playlist->save($this->request->data)) {
                $this->Flash->success(__('Playlist renamed: '), array(
                    'params' => array($this->request->data['Playlist']['title'])
                ));
            } else {
                $this->Flash->error(__('Unable to rename the playlist: '), array(
                    'params' => array($this->request->data['Playlist']['title'])
                ));
            }

            $this->redirect(array('controller' => 'playlists', 'action' => 'index'));
        } else {
            throw new MethodNotAllowedException();
        }
    }

    /**
     * Manage playlists deletion.
     *
     * @param int $id The playlist ID to delete.
     */
    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }

        $playlist = $this->Playlist->read(null, $id);

        if ($this->Playlist->delete($id)) {
            $this->Flash->success(__('Playlist deleted: '), array(
                'params' => array($playlist['Playlist']['title'])
            ));
        } else {
            $this->Flash->error(__('Unable to delete the playlist: '), array(
                'params' => array($playlist['Playlist']['title'])
            ));
        }
        return $this->redirect(array('action' => 'index'));
    }
}