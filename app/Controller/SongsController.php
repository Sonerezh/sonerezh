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
     * Extract and import metadata in database.
     * Sonerezh uses the Getid3 PHP media file parser to extract useful information from MP3 & other multimedia formats.
     *
     * @link http://getid3.sourceforge.net/
     * @see SongsController::import
     */
    protected function _importSong($song) {

       //$song = $this->request->query['path'];
        $getID3 = new getID3();
        $songInfo = $getID3->analyze($song);
        getid3_lib::CopyTagsToComments($songInfo);
        $newSong = array();

        // Parse file metadata to $newSong array.
        if (isset($songInfo['comments'])) {
            if (!empty($songInfo['comments']['title'])) {
                $title_array_length = count($songInfo['comments']['title']);
                $newSong['title'] = $songInfo['comments']['title'][$title_array_length - 1];
            } elseif (!empty($songInfo['filename'])) {
                $newSong['title'] = $songInfo['filename'];
            } else {
                $newSong['title'] = $song;
            }

            if (!empty($songInfo['comments']['artist'])) {
                $artist_array_length = count($songInfo['comments']['artist']);
                $newSong['artist'] = $songInfo['comments']['artist'][$artist_array_length - 1];
            } else {
                $newSong['artist'] = 'Unknown Artist';
            }

            if (!empty($songInfo['comments']['band'])) { // MP3 ID3 Tag
                $band_array_length = count($songInfo['comments']['band']);
                $newSong['band'] = $songInfo['comments']['band'][$band_array_length - 1];
            } elseif (!empty($songInfo['comments']['ensemble'])) { // OGG Tag
                $newSong['band'] = $songInfo['comments']['ensemble'][0];
            } elseif (!empty($songInfo['comments']['albumartist'])) { // OGG / FLAC Tag
                $newSong['band'] = $songInfo['comments']['albumartist'][0];
            } elseif (!empty($songInfo['comments']['album artist'])) { // OGG / FLAC Tag
                $newSong['band'] = $songInfo['comments']['album artist'];
            }

            if (!empty($songInfo['comments']['album'])) {
                $album_array_length = count($songInfo['comments']['album']);
                $newSong['album'] = $songInfo['comments']['album'][$album_array_length - 1];
            } else {
                $newSong['album'] = 'Unknown Album';
            }

            if (!empty($songInfo['comments']['track_number'])) { // MP3 Tag
                $track_number_array = explode('/', (string)$songInfo['comments']['track_number'][0]);
                $newSong['track_number'] = intval($track_number_array[0]);
            } elseif (!empty($songInfo['comments']['tracknumber'])) { // OGG Tag
                $newSong['track_number'] = intval($songInfo['comments']['tracknumber'][0]);
            }

            if (!empty($songInfo['playtime_string'])) {
                $newSong['playtime'] = $songInfo['playtime_string'];
            }

            if (!empty($songInfo['comments']['year'])) {
                $newSong['year'] = intval($songInfo['comments']['year'][0]);
            }

            if (!empty($songInfo['comments']['part_of_a_set'])) { // MP3 Tag
                $newSong['disc'] = $songInfo['comments']['part_of_a_set'][0];
            } elseif (!empty($songInfo['comments']['discnumber'])) { // OGG Tag
                $newSong['disc'] = $songInfo['comments']['discnumber'][0];
            }

            if (!empty($songInfo['comments']['genre'])) {
                $genre_array_length = count($songInfo['comments']['genre']);
                $newSong['genre'] = $songInfo['comments']['genre'][$genre_array_length - 1];
            }

            if (isset($songInfo['comments']['picture'])) {
                if (isset($songInfo['comments']['picture'][0]['image_mime'])) {
                    $mime = preg_split('/\//', $songInfo['comments']['picture'][0]['image_mime']);
                    $extension = $mime[1];
                } else {
                    $extension = 'jpg';
                }

                $name = md5($newSong['artist'].$newSong['album']);
                $path = IMAGES.THUMBNAILS_DIR.DS.$name.'.'.$extension;

                if (!file_exists($path)) {
                    if (!file_exists(IMAGES.THUMBNAILS_DIR)) {
                        new Folder(IMAGES.THUMBNAILS_DIR, true, 0777);
                    }
                    $file = fopen($path, "w");
                    fwrite($file, $songInfo['comments']['picture'][0]['data']);
                    fclose($file);
                }
                $newSong['cover'] = $name.'.'.$extension;
            }

            $newSong['source_path'] = $song;
            $this->Song->create();
            $this->Song->save($newSong);
        }

        return $newSong['title'];
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
        App::import('Vendor', 'Getid3/getid3');
        App::uses('Folder', 'Utility');

        $this->loadModel('Setting');
        $this->Setting->contain('Rootpath');
        $settings = $this->Setting->find('first');

        if ($this->request->is("get")) {

            if ($settings) {
                $paths = $settings['Rootpath'];
            } else {
                $this->Flash->error(__('Please define a root path.'));
                $this->redirect(array('controller' => 'settings', 'action' => 'index'));
            }

            $songs = array();

            foreach ($paths as $path) {
                $dir = new Folder($path['rootpath']);
                $songs = array_merge($songs, $dir->findRecursive('^.*\.(mp3|ogg|flac|aac)$'));
            }

            $existingSongs = $this->Song->find('list', array(
                'fields' => array('Song.id', 'Song.source_path')
            ));
            $new = array_merge(array_diff($songs, $existingSongs));
            $this->Session->write('song_list', $new);

            $this->set('newSongsTotal', count($new));
        } elseif ($this->request->is("post")) {
            $songs = $this->Session->read('song_list');
            $imported = array();
            $count = 0;
            foreach ($songs as $song) {
                $imported[] = $song;
                $this->_importSong($song);
                if ($count >= 100) break;
                $count++;
            }
            if ($count) {
                $settings['Setting']['sync_token'] = time();
                $this->Setting->save($settings);
            }
            echo $settings['Setting']['sync_token'];
            $diff = array_diff($songs, $imported);
            $this->Session->write('song_list', $diff);
            $this->layout = null;
            $this->render(false);
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
                    'order'     => 'Song.album',
                    'limit'     => 36
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
                    'order' => 'Song.album',
                    'limit' => 36
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
            $this->Flash->info('<strong>'.__('Oops!').'</strong> '.__('The database is empty...'));
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
            $this->Flash->info("<strong>".__('Oops!')."</strong> ".__('The database is empty...'));
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
            $this->Flash->info("<strong>".__('Oops!')."</strong> ".__('The database is empty...'));
        }

        $this->set(compact('songs', 'playlists'));
    }

    /**
     * Search view function
     * We just make a SQL request...
     */
    public function search() {
        $query = isset($this->request->query['q']) ? $this->request->query['q'] : false ;

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
                $this->Flash->error("<strong>".__('Oops!')."</strong> ".__('No results.'));
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
                $avconv = "ffmpeg";
                if (shell_exec("which avconv")) {
                    $avconv = "avconv";
                }
                if ($settings['Setting']['convert_to'] == 'mp3') {
                    $path = TMP.date('YmdHis').".mp3";
                    $song['Song']['path'] = $path;
                    passthru($avconv.' -i "'.$song['Song']['source_path'].'" -threads 4  -c:a libmp3lame -b:a '.$bitrate.'k "'.$path.'" 2>&1');
                } elseif ($settings['Setting']['convert_to'] == 'ogg') {
                    $path = TMP.date('YmdHis').".ogg";
                    $song['Song']['path'] = $path;
                    passthru($avconv.' -i "'.$song['Song']['source_path'].'" -threads 4  -c:a libvorbis -q:a '.$bitrate.' "'.$path.'" 2>&1');
                }
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
