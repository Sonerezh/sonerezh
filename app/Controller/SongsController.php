<?php

App::uses("AppController", "Controller");

/**
 * @property Song $Song
 */
class SongsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    public function sync() {
        $this->viewClass = 'Json';
        $this->SortComponent = $this->Components->load('Sort');

        $songs = $this->Song->find("all", array('fields' => array('id', 'album', 'artist', 'band', 'cover', 'title', 'disc', 'track_number', 'playtime'), 'order' => 'title'));
        $songs = $this->SortComponent->sortByBand($songs);
        foreach ($songs as $k => &$song) {
            $song['Song']['url'] = $this->request->base . '/songs/download/' . $song['Song']['id'];
            $song['Song']['cover'] = $this->request->base.'/'.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover']);
        }
        $songs = Hash::extract($songs, '{n}.Song');

        $this->set('data', $songs);
        $this->set('_serialize', 'data');
    }

    /**
     * The albums view function.
     * Find songs in the database, alphabetically and grouped by album.
     */
    public function albums() {

        $sort = 'Song.album';
        $user_preferences = json_decode($this->Auth->user('preferences'), true);

        if (!empty($user_preferences['albums_sort']) && $user_preferences['albums_sort'] == 'band') {
            $sort = 'Song.band, Song.album';
        }

        if (isset($this->request->query['sort'])) {
            if ($this->request->query['sort'] == 'band') {

                $sort = 'Song.band, Song.album';

                if (!isset($user_preferences['sort']) || $user_preferences['sort'] != 'band') {
                    $this->loadModel('User');
                    $user = $this->User->find('first', array(
                        'fields' => array('id', 'preferences'),
                        'conditions' => array('id' => $this->Auth->user('id')),
                        )
                    );

                    $new_preferences = json_decode($user['User']['preferences'], true);
                    $new_preferences['albums_sort'] = 'band';
                    $user['User']['preferences'] = json_encode($new_preferences);

                    if ($this->User->save($user, false)) {
                        $session_auth = $this->Session->read('Auth');
                        $session_auth['User']['preferences'] = $user['User']['preferences'];
                        $this->Session->write('Auth', $session_auth);
                    }
                }

            } elseif ($this->request->query['sort'] == 'album') {

                $sort = 'Song.album';

                if (!isset($user_preferences['sort']) || $user_preferences['sort'] != 'album') {
                    $this->loadModel('User');
                    $user = $this->User->find('first', array(
                            'fields' => array('id', 'preferences'),
                            'conditions' => array('id' => $this->Auth->user('id')),
                        )
                    );

                    $new_preferences = json_decode($user['User']['preferences'], true);
                    unset($new_preferences['albums_sort']);
                    $user['User']['preferences'] = json_encode($new_preferences);

                    if ($this->User->save($user, false)) {
                        $session_auth = $this->Session->read('Auth');
                        $session_auth['User']['preferences'] = $user['User']['preferences'];
                        $this->Session->write('Auth', $session_auth);
                    }
                }
            }
        }

        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        $latests = array();
        // Is this the first page requested?
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : 1;
        $db = $this->Song->getDataSource();

        $this->Song->virtualFields['cover'] = 'MIN(Song.cover)';

        if ($page == 1) {
            $latests = $this->Song->find('all', array(
                'fields' => array('Song.band', 'Song.album', 'cover'),
                'group' => array('Song.album', 'Song.band'),
                'order' => 'MAX(Song.created) DESC',
                'limit' => 6
            ));
        }

        $this->Paginator->settings = array(
            'Song' => array(
                'fields' => array('Song.band', 'Song.album', 'cover'),
                'group' => array('Song.album', 'Song.band'),
                'order' => $sort,
                'limit' => 36
            )
        );

        $songs = $this->Paginator->paginate();

        foreach ($songs as &$song) {
            $song['Song']['cover'] = empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover'];
        }

        foreach ($latests as &$latest) {
            $latest['Song']['cover'] = empty($latest['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$latest['Song']['cover'];
        }

        if (empty($songs)) {
            $this->Flash->info(__('Oops! The database is empty...'));
        }

        $this->set(compact('songs', 'playlists', 'latests'));
    }

    /**
     * Get album content.
     * This function is called when you click on a cover from the albums view.
     */
    public function album() {
        $band = $this->request->query('band');
        $album = $this->request->query('album');
        $songs = $this->Song->find('all', array(
                'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.artist', 'Song.band', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc'),
                'conditions'    => array('Song.band' => $band, 'Song.album' => $album)
            )
        );

        $this->SortComponent = $this->Components->load('Sort');
        $songs = $this->SortComponent->sortByDisc($songs);

        $parsed = array();
        foreach ($songs as &$song) {
            $currentDisc = 1;
            if (!empty($song['Song']['disc'])) {
                $setsQuantity = explode('/', $song['Song']['disc']);
                $currentDisc = (int)($setsQuantity[0]);
            }

            $parsed[$currentDisc][] = $song;
        }

        $this->set(array('songs' => $parsed, 'band' => $band, 'album' => $album));
    }

    /**
     * The artists view function.
     * Generate a list of 5 bands, in alphabetical order. This list is then read to find all the songs of each band, grouped by album and disc.
     */
    public function artists() {
        $this->loadModel('Playlist');
        $this->Playlist->recursive = 0;

        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        // Get 5 band names
        $this->Paginator->settings = array(
            'Song' => array(
                'limit'     => 5,
                'fields'    => array('Song.band'),
                'group'     => array('Song.band'),
                'order'     => array('Song.band' => 'ASC')
            )
        );

        $bands = $this->Paginator->paginate();

        $band_list = array();
        foreach ($bands as $band) {
            $band_list[] = $band['Song']['band'];
        }

        // Get songs from the previous band names
        $songs = $this->Song->find('all', array(
            'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.artist', 'Song.cover', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.genre'),
            'conditions'    => array('Song.band' => $band_list)
        ));

        $this->SortComponent = $this->Components->load('Sort');
        $songs = $this->SortComponent->sortByBand($songs);

        // Then we can group the songs by band name, album and disc.
        $parsed = array();
        foreach ($songs as $song) {
            $currentDisc = 1;
            if (!empty($song['Song']['disc'])) {
                $setsQuantity = explode('/', $song['Song']['disc']);
                $currentDisc = (int)($setsQuantity[0]);
            }

            if (!isset($parsed[$song['Song']['band']]['albums'][$song['Song']['album']])) {
                $parsed[$song['Song']['band']]['albums'][$song['Song']['album']] = array(
                    'album' => $song['Song']['album'],
                    'cover' => empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover'],
                    'year'  => $song['Song']['year'],
                    'genre' => array(),
                );
            }

            if (!in_array($song['Song']['genre'], $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['genre'])) {
                $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['genre'][] = $song['Song']['genre'];
            }

            if (!isset($parsed[$song['Song']['band']]['sCount'])) {
                $parsed[$song['Song']['band']]['sCount'] = 1;
            } else {
                $parsed[$song['Song']['band']]['sCount'] += 1;
            }

            $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['discs'][$currentDisc]['songs'][] = $song['Song'];
        }

        if (empty($parsed)) {
            $this->Flash->info(__('Oops! The database is empty...'));
        }
        $this->set(array('songs' => $parsed, 'playlists' => $playlists));
    }

    /**
     * The index view function
     * Get songs from database, ordered by artist.
     */
    public function index() {
        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        // Get 5 band names
        $this->Paginator->settings = array(
            'Song' => array(
                'limit'     => 5,
                'fields'    => array('Song.band'),
                'group'     => array('Song.band'),
                'order'     => array('Song.band' => 'ASC')
            )
        );

        $bands = $this->Paginator->paginate();

        $band_list = array();
        foreach ($bands as $band) {
            $band_list[] = $band['Song']['band'];
        }

        // Get songs from the previous band names
        $songs = $this->Song->find('all', array(
            'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.artist', 'Song.cover', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.genre'),
            'conditions'    => array('Song.band' => $band_list)
        ));

        $this->SortComponent = $this->Components->load('Sort');
        $songs = $this->SortComponent->sortByBand($songs);

        if (empty($songs)) {
            $this->Flash->info(__('Oops! The database is empty...'));
        }

        $this->set(compact('songs', 'playlists'));
    }

    /**
     * Search view function
     * We just make a SQL request...
     */
    public function search() {
        $query = isset($this->request->query['q']) ? trim($this->request->query['q']) : false ;

        if ($query) {
            $this->Paginator->settings = array(
                'Song' => array(
                    'fields'        => array('Song.band'),
                    'group'         => array('Song.band'),
                    'limit'         => 5,
                    'conditions'    => array('OR' => array(
                        'LOWER(Song.title) like'   => '%'.strtolower($query).'%',
                        'LOWER(Song.band) like'    => '%'.strtolower($query).'%',
                        'LOWER(Song.artist) like'  => '%'.strtolower($query).'%',
                        'LOWER(Song.album) like'   => '%'.strtolower($query).'%'
                        )
                    )
                )
            );

            $bands = $this->Paginator->paginate();
            $band_list = array();

            foreach ($bands as $band) {
                $band_list[] = $band['Song']['band'];
            }

            $songs = $this->Song->find('all', array(
                    'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.artist', 'Song.cover', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.genre'),
                    'conditions'    => array(
                    'OR' => array(
                        'LOWER(Song.title) like'   => '%'.strtolower($query).'%',
                        'LOWER(Song.artist) like'  => '%'.strtolower($query).'%',
                        'LOWER(Song.album) like'   => '%'.strtolower($query).'%'
                        ),
                    'Song.band' => $band_list
                    )
                )
            );

            $this->SortComponent = $this->Components->load('Sort');
            $songs = $this->SortComponent->sortByBand($songs);

            $parsed = array();
            foreach ($songs as $song) {
                $currentDisc = 1;
                if (!empty($song['Song']['disc'])) {
                    $setsQuantity = explode('/', $song['Song']['disc']);
                    $currentDisc = (int)($setsQuantity[0]);
                }

                if (!isset($parsed[$song['Song']['band']]['albums'][$song['Song']['album']])) {
                    $parsed[$song['Song']['band']]['albums'][$song['Song']['album']] = array(
                        'album' => $song['Song']['album'],
                        'cover' => empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover'],
                        'year'  => $song['Song']['year'],
                        'genre' => array()
                    );
                }

                if (!in_array($song['Song']['genre'], $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['genre'])) {
                    $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['genre'][] = $song['Song']['genre'];
                }

                if (!isset($parsed[$song['Song']['band']]['sCount'])) {
                    $parsed[$song['Song']['band']]['sCount'] = 1;
                } else {
                    $parsed[$song['Song']['band']]['sCount'] += 1;
                }

                $parsed[$song['Song']['band']]['albums'][$song['Song']['album']]['discs'][$currentDisc]['songs'][] = $song['Song'];

            }

            if (empty($parsed)) {
                $this->Flash->error(__('Oops! No results.'));
            }
            $this->set('songs', $parsed);
        }

        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));
        $this->set(compact('query', 'playlists'));
    }

    /**
     * This function is called by the player when you click on 'Play'
     * The file extension is checked to know if Sonerezh must converts the track.
     *
     * @param null $id
     * @return CakeResponse audio file
     */
    public function download($id = null) {
        $this->loadModel('Setting');
        $settings = $this->Setting->find('first');

        $song = $this->Song->findById($id);
        if (!$song) {
            throw new NotFoundException();
        }

        if (empty($song['Song']['path'])) {
            $file_extension = substr(strrchr($song['Song']['source_path'], "."), 1);
        } else {
            $file_extension = substr(strrchr($song['Song']['path'], "."), 1);
        }

        if (empty($song['Song']['path']) || $file_extension != $settings['Setting']['convert_to']) {
            if (in_array($file_extension, explode(',', $settings['Setting']['convert_from']))) {
                $bitrate = $settings['Setting']['quality'];
                $avconv = 'ffmpeg';

                if (shell_exec('which avconv') || shell_exec('where avconv')) {
                    $avconv = 'avconv';
                }

                $orig_locale = setlocale(LC_ALL, 0);
                $orig_locale_bis = str_replace(';', '&', $orig_locale);
                parse_str($orig_locale_bis, $locale_array);

                setlocale(LC_CTYPE, 'C.UTF-8');

                if ($settings['Setting']['convert_to'] == 'mp3') {
                    $path = TMP.date('YmdHis').".mp3";
                    $song['Song']['path'] = $path;
                    passthru($avconv . " -i " . escapeshellarg($song['Song']['source_path']) . " -threads 4 -c:a libmp3lame -b:a " . escapeshellarg($bitrate . 'k') . " " . escapeshellarg($path) . " 2>&1");
                } elseif ($settings['Setting']['convert_to'] == 'ogg') {
                    $path = TMP.date('YmdHis').".ogg";
                    $song['Song']['path'] = $path;
                    passthru($avconv . " -i " . escapeshellarg($song['Song']['source_path']) . " -threads 4 -c:a libvorbis -q:a " . escapeshellarg($bitrate) . " " . escapeshellarg($path) . " 2>&1");
                }

                setlocale(LC_CTYPE, $locale_array['LC_CTYPE']);

            } elseif (empty($song['Song']['path'])) {
                $song['Song']['path'] = $song['Song']['source_path'];
            }

            $this->Song->id = $id;
            $this->Song->save($song);
        }

        // Symlink files whose name contains '..' to avoid CakePHP request error.
        if (strpos($song['Song']['path'], '..') !== false) {
            $symlinkPath = TMP.md5($song['Song']['path']).'.'.substr(strrchr($song['Song']['path'], "."), 1);
            if (!file_exists($symlinkPath)) {
                symlink($song['Song']['path'], $symlinkPath);
            }
            $song['Song']['path'] = $symlinkPath;
        }

        $this->response->file($song['Song']['path'], array('download' => true));
        $this->response->cache('-1 minute', '+2 hours');
        return $this->response;
    }

    public function ajax_view($id) {
        $this->viewClass = 'Json';
        $song = $this->Song->find('first', array('conditions' => array('Song.id' => $id)));
        $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
        $song['Song']['cover'] = $this->request->base.'/'.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover']);

        $this->set('data', $song);
        $this->set('_serialize', 'data');
    }

    public function ajax_artist() {
        $this->viewClass = 'Json';
        $artist = $this->request->query('artist');
        $songs = $this->Song->find('all', array(
            'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.artist', 'Song.cover', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.genre'),
            'conditions' => array(
                'Song.band' => $artist
            )
        ));

        $this->SortComponent = $this->Components->load('Sort');
        $songs = $this->SortComponent->sortByBand($songs);

        foreach ($songs as &$song) {
            $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover']);
        }

        $this->set('data', $songs);
        $this->set('_serialize', 'data');
    }

    public function ajax_album() {
        $this->viewClass = 'Json';
        $artist = $this->request->query('artist');
        $album = $this->request->query('album');
        $songs = $this->Song->find('all', array(
            'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.artist', 'Song.band', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.cover'),
            'conditions' => array(
                'Song.band' => $artist,
                'Song.album' => $album
            )
        ));

        $this->SortComponent = $this->Components->load('Sort');
        $songs = $this->SortComponent->sortByDisc($songs);

        foreach ($songs as &$song) {
            $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover']);
        }

        $this->set('data', $songs);
        $this->set('_serialize', 'data');
    }

    public function ajax_playlist() {
        $this->viewClass = 'Json';
        $playlist = $this->request->query('playlist');
        $this->Song->PlaylistMembership->contain('Song');
        $songs = $this->Song->PlaylistMembership->find('all', array(
            'conditions' => array(
                'PlaylistMembership.playlist_id' => $playlist
            ),
            'order' => array('PlaylistMembership.sort')
        ));
        foreach ($songs as &$song) {
            unset($song['PlaylistMembership']);
            $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.'/'.$song['Song']['cover']);
        }

        $this->set('data', $songs);
        $this->set('_serialize', 'data');
    }
}
