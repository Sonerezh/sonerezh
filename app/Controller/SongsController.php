<?php

App::uses("AppController", "Controller");

/**
 * @property Song $Song
 */
class SongsController extends AppController {

    public function beforeFilter() {
        parent::beforeFilter();
    }

    /**
     * The import view function.
     * The function does the following action:
     *      - Check the root path,
     *      - Search every media files (mp3, ogg, flac, aac) to load them in an array
     *      - Compare this array with the list of existing songs to keep only new tracks
     *      - Pass this array to the view.
     *
     * @see SongsController::_importSong
     */
    public function import() {
        App::uses('Folder', 'Utility');
        App::uses('SongManager', 'SongManager');

        $this->loadModel('Setting');
        $this->Setting->contain('Rootpath');
        $settings = $this->Setting->find('first');

        if ($this->request->is('get')) {

            if ($settings) {
                $paths = $settings['Rootpath'];
            } else {
                $this->Flash->error(__('Please define a root path.'));
                $this->redirect(array('controller' => 'settings', 'action' => 'index'));
            }

            // The files found via Folder->findRecursive()
            $found = array();

            foreach ($paths as $path) {
                $dir = new Folder($path['rootpath']);
                $found = array_merge($found, $dir->findRecursive('^.*\.(mp3|ogg|flac|aac)$'));
            }

            // The files already imported
            $already_imported = $this->Song->find('list', array(
                'fields' => array('Song.id', 'Song.source_path')
            ));

            // The difference between $found and $already_imported
            $to_import = array_merge(array_diff($found, $already_imported));
            $to_import_count = count($to_import);
            $found_count = count($found);
            $diff_count = $found_count - $to_import_count;
            $this->Session->write('to_import', $to_import);
            $this->set(compact('to_import_count', 'diff_count'));
        } elseif ($this->request->is('post')) {
            $this->viewClass = 'Json';
            $import_result = array();

            if (Cache::read('import')) { // Read lock to avoid multiple import processes in the same time
                $import_result[0]['status'] = 'ERR';
                $import_result[0]['message'] = __('The import process is already running via another client or the CLI.');
                $this->set(compact('import_result'));
                $this->set('_serialize', array('import_result'));
            } else {
                // Write lock
                Cache::write('import', true);

                $to_import = $this->Session->read('to_import');
                $imported = array();

                $i = 0;
                foreach ($to_import as $file) {
                    $song_manager = new SongManager($file);
                    $parse_result = $song_manager->parseMetadata();

                    $this->Song->create();
                    if (!$this->Song->save($parse_result['data'])) {
                        $import_result[$file]['status'] = 'ERR';
                        $import_result[$file]['message'] = __('Unable to save the song metadata to the database');
                    } else {
                        unset($parse_result['data']);
                        $import_result[$i]['file'] = $file;
                        $import_result[$i]['status'] = $parse_result['status'];
                        $import_result[$i]['message'] = $parse_result['message'];
                    }

                    if ($i >= 100) {
                        break;
                    }

                    $imported [] = $file;
                    $i++;
                }

                if ($i) {
                    $settings['Setting']['sync_token'] = time();
                    $this->Setting->save($settings);
                }

                // Delete lock
                Cache::delete('import');

                $sync_token = $settings['Setting']['sync_token'];
                $diff = array_diff($to_import, $imported);
                $this->Session->write('to_import', $diff);
                $this->set(compact('sync_token', 'import_result'));
                $this->set('_serialize', array('sync_token', 'import_result'));
            }

        }
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
        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        $latests = array();
        // Is this the first page requested?
        $page = isset($this->request->params['named']['page']) ? $this->request->params['named']['page'] : 1;
        $db = $this->Song->getDataSource();

        // Ugly temporary fix for SQlite DB
        if ($db->config['datasource'] == 'Database/Sqlite') {

            if ($page == 1) {
                $latests = $this->Song->find('all', array(
                    'fields' => array('Song.id', 'Song.band', 'Song.album', 'Song.cover'),
                    'group' => 'Song.album',
                    'order' => 'Song.created DESC',
                    'limit' => 6
                ));
            }

            $this->Paginator->settings = array(
                'Song' => array(
                    'fields'    => array('Song.id', 'Song.band', 'Song.album', 'Song.cover'),
                    'group'     => 'Song.album',
                    'order'     => ['Song.band', 'Song.album'],
                    'limit'     => 60
                )
            );
        } else {
            $subQuery = $db->buildStatement(
                array(
                    'fields' => array('MIN(subsong.id)', 'subsong.album'),
                    'table' => $db->fullTableName($this->Song),
                    'alias' => 'subsong',
                    'group' => 'subsong.album'
                ),
                $this->Song
            );
            $subQuery = ' (Song.id, Song.album) IN (' . $subQuery . ') ';

            if ($page == 1) {
                $latests = $this->Song->find('all', array(
                    'fields' => array('Song.id', 'Song.band', 'Song.album', 'Song.cover'),
                    'conditions' => $subQuery,
                    'order' => 'Song.created DESC',
                    'limit' => 6
                ));
            }

            // This doesn't work on SQlite database
            $this->Paginator->settings = array(
                'Song' => array(
                    'fields' => array('Song.id', 'Song.band', 'Song.album', 'Song.cover'),
                    'conditions' => $subQuery,
                    'order' => ['Song.band', 'Song.album'],
                    'limit' => 60
                )
            );
        }

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
            $setsQuantity = explode('/', $song['Song']['disc']);

            if (count($setsQuantity) < 2 || $setsQuantity[1] == '1') {
                $currentDisc = '1';
            } else {
                $currentDisc = $setsQuantity[0];
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
            $setsQuantity = preg_split('/\//', $song['Song']['disc']);

            if (count($setsQuantity) < 2 || $setsQuantity[1] == '1') {
                $currentDisc = '1';
            } else {
                $currentDisc = $setsQuantity[0];
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
                        'Song.title like'   => '%'.$query.'%',
                        'Song.band like'    => '%'.$query.'%',
                        'Song.artist like'  => '%'.$query.'%',
                        'Song.album like'   => '%'.$query.'%'
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
                        'Song.title like'   => '%'.$query.'%',
                        'Song.artist like'  => '%'.$query.'%',
                        'Song.album like'   => '%'.$query.'%'
                        ),
                    'Song.band' => $band_list
                    )
                )
            );

            $this->SortComponent = $this->Components->load('Sort');
            $songs = $this->SortComponent->sortByBand($songs);

            $parsed = array();
            foreach ($songs as $song) {
                $setsQuantity = preg_split('/\//', $song['Song']['disc']);

                if (count($setsQuantity) < 2 || $setsQuantity[1] == '1'  ) {
                    $currentDisc = '1';
                } else {
                    $currentDisc = $setsQuantity[0];
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
     * Guillaume: Altered version of /lib/Cake/Network/CakeResponse.php _sendFile function
     */
    public function streamFile($path, $format, $bitrate) {
        $minBuffer = 256 * $bitrate;
        while (!file_exists($path)) {}
        while (filesize($path) <= $minBuffer) { clearstatcache(); }
        $file = fopen($path, 'rb');
        set_time_limit(0);
        ob_end_clean();
        if (ob_get_level() == 0) ob_start();
        $readPos = 0;
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Content-Type: audio/" . $format);
        while (!feof($file)) {
            clearstatcache();
            $currentSize = filesize($path);
            $length = $currentSize - $readPos;
            if ($length) $buffer = fread($file, $length);
            echo $buffer;
            $readPos += strlen($buffer);
            if (ob_get_level()) ob_flush();
            flush();
            sleep(1);
        }
        if (ob_get_length()) ob_end_flush();
        fclose($file);
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
			$converted = false;
            if (in_array($file_extension, explode(',', $settings['Setting']['convert_from']))) {
                $bitrate = $settings['Setting']['quality'];
                $avconv = "ffmpeg";
                if (shell_exec("which avconv")) {
                    $avconv = "avconv";
                }
                if ($settings['Setting']['convert_to'] == 'mp3') {
                    $path = TMP.date('YmdHis').".mp3";
                    $song['Song']['path'] = $path;
                    //passthru($avconv . " -i " . escapeshellarg($song['Song']['source_path']) . "' -threads 4  -c:a libmp3lame -b:a " . escapeshellarg($bitrate) . "k " . escapeshellarg($path) . " 2>&1");
                    exec('bash -c "exec nohup setsid \"'.$avconv.'\" -i \"'.$song['Song']['source_path'].'\" -threads 3 -c:a libmp3lame -q:a 2 \"'.$path.'\" > /dev/null 2>&1 &"');
                    $converted = true;
                } elseif ($settings['Setting']['convert_to'] == 'ogg') {
                    $path = TMP.date('YmdHis').".ogg";
                    $song['Song']['path'] = $path;
                    //passthru($avconv . " -i " . escapeshellarg($song['Song']['source_path']) . " -threads 4 -c:a libvorbis -q:a " . escapeshellarg($bitrate) . " " . escapeshellarg($path) . " 2>&1");
                    exec('bash -c "exec nohup setsid \"'.$avconv.'\" -i \"'.$song['Song']['source_path'].'\" -threads 3 -c:a libvorbis -q:a '.$bitrate.' \"'.$path.'\" > /dev/null 2>&1 &"');
                    $converted = true;
                }
            } elseif (empty($song['Song']['path'])) {
                $song['Song']['path'] = $song['Song']['source_path'];
            }

            $this->Song->id = $id;
            $this->Song->save($song);
            if ($converted) return $this->streamFile($song['Song']['path'], $settings['Setting']['convert_to'], $bitrate);
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
