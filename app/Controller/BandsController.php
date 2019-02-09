<?php

App::uses('AppController', 'Controller');

class BandsController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function index()
    {
        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields' => array('Playlist.id', 'Playlist.title'),
            'conditions' => array('user_id' => AuthComponent::user('id')),
            'recursive' => 0
        ));

        $this->Paginator->settings = array(
            'limit' => 5,
            'fields' => array('Band.id', 'Band.name'),
            'order' => array('Band.name' => 'ASC'),
            'contain' => array(
                'Album' => array(
                    'fields' => array('id', 'name', 'cover', 'year'),
                    'order' => 'year',
                    'Track' => array(
                        'fields' => array(
                            'id', 'title', 'source_path', 'playtime',
                            'track_number', 'disc_number', 'genre', 'artist'
                        ),
                        'conditions' => array('imported' => true),
                        'order' => 'track_number'
                    ),
                )
            )
        );

        $bands = $this->Paginator->paginate();

        if (empty($bands)) {
            $this->Flash->info(__('Oops! The database is empty…'));
        } else {
            foreach ($bands as $b => $band) {
                $tracksCount = 0;
                $albumGenres = array();

                foreach ($band['Album'] as $a => $album) {
                    $tracksCount += count($album['Track']);
                    $discs = array();

                    foreach ($album['Track'] as $track) {
                        if (!empty($track['genre']) && !in_array($track['genre'], $albumGenres)) {
                            $albumGenres[] = $track['genre'];
                        }

                        if (empty($track['disc_number'])) {
                            $discs[1]['Track'][] = $track;
                        } else {
                            $discs[$track['disc_number']]['Track'][] = $track;
                        }
                    }

                    ksort($discs);
                    $bands[$b]['Album'][$a]['discs'] = $discs;
                    $bands[$b]['Album'][$a]['genres'] = $albumGenres;
                    unset($bands[$b]['Album'][$a]['Track']);
                }

                $bands[$b]['Band']['tracks_count'] = $tracksCount;
            }
        }

        $this->set(compact('bands', 'playlists'));
    }

    /**
     * A function to replace the legacy Javascript calls to the IndexedDB.
     */
    public function api_tracks($bandId)
    {
        $this->viewClass = 'Json';

        $raw = $this->Band->find('first', array(
            'conditions' => array('Band.id' => $bandId),
            'contain' => array(
                'Album' => array(
                    'fields' => array('Album.cover', 'Album.name', 'Album.year'),
                    'order' => 'Album.name',
                    'Track' => array(
                        'fields' => array(
                            'Track.id', 'Track.title', 'Track.playtime',
                            'Track.track_number', 'Track.disc_number',
                            'Track.artist'
                        ),
                        'conditions' => array('imported' => true),
                        'order' => array(
                            'Track.disc_number' => 'ASC',
                            'Track.track_number' => 'ASC'
                        )
                    )
                )
            )
        ));

        $data = array();
        foreach ($raw['Album'] as $album) {
            foreach ($album['Track'] as $track) {
                $data[] = array(
                    'id' => $track['id'],
                    'title' => $track['title'],
                    'artist' => $track['artist'],
                    'band' => $raw['Band']['name'],
                    'album' => $album['name'],
                    'cover' => $album['cover'],
                    'disc_number' => $track['disc_number'],
                    'track_number' => $track['track_number'],
                    'playtime' => $track['playtime'],
                    'url' => $this->request->base . '/tracks/download/' . $track['id']
                );
            }
        }

        if (empty($data)) {
            $this->response->statusCode(404);
        }

        $this->set(compact('data'));
        $this->set('_serialize', 'data');
    }
}