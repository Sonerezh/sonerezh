<?php

App::uses('AppController', 'Controller');

class AlbumsController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function index()
    {
        if (isset($this->request->query['sort']) && $this->request->query['sort'] == 'band') {
            $order = array('Band.name', 'Album.name');
        } else {
            $order = array('Album.name');
        }

        // $latest is displayed on the first page only.
        if (!isset($this->request->params['named']['page'])) {
            $latest = $this->Album->find('all', array(
                'fields' => array('Album.id', 'Album.name', 'Album.cover'),
                'order' => array('Album.created'),
                'limit' => 6,
                'contain' => array(
                    'Band' => array(
                        'fields' => array('Band.name')
                    )
                )
            ));
        }

        $this->Paginator->settings = array(
            'fields' => array('Album.id', 'Album.name', 'Album.cover'),
            'order' => $order,
            'limit' => 36,
            'contain' => array(
                'Band' => array(
                    'fields' => array('Band.name')
                )
            )
        );

        $albums = $this->Paginator->paginate();

        if (empty($albums)) {
            $this->Flash->info(__('Oops! The database is empty…'));
        }

        $this->set(compact('albums', 'latest'));
    }

    public function album($id)
    {
        $album = $this->Album->find('first', array(
            'fields' => array('Album.id', 'Album.name', 'Album.year'),
            'conditions' => array('Album.id' => $id),
            'contain' => array(
                'Band' => array(
                    'fields' => array('Band.name')
                ),
                'Track' => array(
                    'fields' => array(
                        'Track.id', 'Track.artist', 'Track.title',
                        'Track.playtime', 'Track.track_number',
                        'Track.disc_number'
                    ),
                    'conditions' => array('Track.imported' => true),
                    'order' => array('Track.track_number')
                )
            )
        ));

        $discs = array();
        foreach ($album['Track'] as $track) {
            if (empty($track['disc_number'])) {
                $discs[1]['Track'][] = $track;
            } else {
                $discs[$track['disc_number']]['Track'][] = $track;
            }
        }
        unset($album['Track']);
        ksort($discs);
        $album['discs'] = $discs;

        $this->set(compact('album'));
    }

    /**
     * A function to replace the legacy Javascript calls to the IndexedDB.
     */
    public function api_tracks($albumId)
    {
        $this->viewClass = 'Json';

        $raw = $this->Album->find('first', array(
            'fields' => array('Album.id', 'Album.name', 'Album.cover'),
            'conditions' => array('Album.id' => $albumId),
            'contain' => array(
                'Band' => array(
                    'fields' => array('Band.name'),
                ),
                'Track' => array(
                    'fields' => array(
                        'Track.id', 'Track.title', 'Track.playtime',
                        'Track.track_number', 'Track.disc_number',
                        'Track.artist'
                    ),
                    'order' => array(
                        'Track.disc_number' => 'ASC',
                        'Track.track_number' => 'ASC'
                    ),
                    'conditions' => array('imported' => true)
                )
            )
        ));

        $data = array();
        foreach ($raw['Track'] as $track) {
            $data[] = array(
                'id' => $track['id'],
                'title' => $track['title'],
                'artist' => $track['artist'],
                'band' => $raw['Band']['name'],
                'album' => $raw['Album']['name'],
                'cover' => $raw['Album']['cover'],
                'disc_number' => $track['disc_number'],
                'track_number' => $track['track_number'],
                'playtime' => $track['playtime'],
                'url' => $this->request->base . '/tracks/download/' . $track['id']
            );
        }

        $this->set(compact('data'));
        $this->set('_serialize', 'data');
    }
}