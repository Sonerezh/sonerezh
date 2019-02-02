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
            'recursive' => 2,
            'contain' => array(
                'Album' => array(
                    'fields' => array('id', 'name', 'cover', 'year'),
                    'order' => 'year',
                    'Track' => array(
                        'fields' => array(
                            'id', 'title', 'source_path', 'playtime', 'track_number', 'disc_number', 'genre', 'artist'
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
            return;
        }

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

                if (empty($album['cover'])) {
                    $bands[$b]['Album'][$a]['cover'] = 'no-cover.png';
                } else {
                    $bands[$b]['Album'][$a]['cover'] = THUMBNAILS_DIR . '/' . $album['cover'];
                }

                $bands[$b]['Album'][$a]['discs'] = $discs;
                $bands[$b]['Album'][$a]['genres'] = $albumGenres;
                unset($bands[$b]['Album'][$a]['Track']);
            }

            $bands[$b]['Band']['tracks_count'] = $tracksCount;
        }

        $this->set(compact('bands', 'playlists'));
    }
}