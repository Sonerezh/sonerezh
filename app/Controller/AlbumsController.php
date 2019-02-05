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

            if (!empty($latest)) {
                foreach ($latest as $a => $album) {
                    if (empty($album['Album']['cover'])) {
                        $latest[$a]['Album']['cover'] = 'no-cover.png';
                    } else {
                        $latest[$a]['Album']['cover'] = implode('/', array(THUMBNAILS_DIR, $album['Album']['cover']));
                    }
                }
            }
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
            return;
        }

        foreach ($albums as $a => $album) {
            if (empty($album['Album']['cover'])) {
                $albums[$a]['Album']['cover'] = 'no-cover.png';
            } else {
                $albums[$a]['Album']['cover'] = implode('/', array(THUMBNAILS_DIR, $album['Album']['cover']));
            }
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
                        'Track.id', 'Track.artist', 'Track.title', 'Track.playtime', 'Track.track_number', 'Track.disc_number'
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
}