<?php

App::uses("AppController", "Controller");

/**
 * @property Song $Song
 */
class SongsController extends AppController{

    /**
     * Extract and import metadata in database.
     * Sonerezh uses the Getid3 PHP media file parser to extract useful information from MP3 & other multimedia formats.
     *
     * @link http://getid3.sourceforge.net/
     * @see SongsController::import
     */
    public function ajax_import(){
        App::import('Vendor', 'Getid3/getid3');
        App::uses('Folder', 'Utility');

        $song = $this->request->query['path'];
        $getID3 = new getID3();
        $songInfo = $getID3->analyze($song);
        getid3_lib::CopyTagsToComments($songInfo);
        $newSong = array();

        // Parse file metadata to $newSong array.
        if (isset($songInfo['comments'])) {
            if (isset($songInfo['comments']['title'])) {
                $newSong['title'] = $songInfo['comments']['title'][0];
            }

            if (isset($songInfo['comments']['artist']) && !empty($songInfo['comments']['artist'])) {
                $newSong['artist'] = $songInfo['comments']['artist'][0];
            } else {
                $newSong['artist'] = 'Unknown Artist';
            }

            if (isset($songInfo['comments']['band'])) {
                $newSong['band'] = $songInfo['comments']['band'][0];
            }

            if (isset($songInfo['comments']['album']) && !empty($songInfo['comments']['album'])) {
                $newSong['album'] = $songInfo['comments']['album'][count($songInfo['comments']['album']) - 1];
            } else {
                $newSong['album'] = 'Unknown Album';
            }

            if (isset($songInfo['comments']['track_number']) && intval($songInfo['comments']['track_number'][0])) {
                $newSong['track_number'] = $songInfo['comments']['track_number'][0];
            }

            if (isset($songInfo['playtime_string'])) {
                $newSong['playtime'] = $songInfo['playtime_string'];
            }

            if (isset($songInfo['comments']['year'])) {
                $newSong['year'] = $songInfo['comments']['year'][0];
            }

            if (isset($songInfo['comments']['part_of_a_set'])) {
                $newSong['disc'] = $songInfo['comments']['part_of_a_set'][0];
            }

            if (isset($songInfo['comments']['genre'])){
                $newSong['genre'] = $songInfo['comments']['genre'][0];
            }

            if (isset($songInfo['comments']['picture'])) {
                if (isset($songInfo['comments']['picture'][0]['image_mime'])) {
                    $mime = preg_split('/\//', $songInfo['comments']['picture'][0]['image_mime']);
                    $extension = $mime[1];
                } else {
                    $extension = 'jpg';
                }

                $name = md5($newSong['artist'].$newSong['album']);
                $path = IMAGES.THUMBNAILS_DIR.$name.'.'.$extension;

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
        }

        if (!isset($newSong['title'])) {
            if (isset($songInfo['filename'])) {
                $newSong['title'] = $songInfo['filename'];
            } else {
                $newSong['title'] = $song;
            }
        }

        $newSong['source_path'] = $song;
        $this->Song->create();
        $this->Song->save($newSong);
        $this->layout = false;
        $this->render(false);

        echo json_encode(array('title' => $newSong['title']));
    }

    /**
     * The import view function.
     * The function does the following action:
     *      - Check the root path,
     *      - Search every media files (mp3, ogg, flac, wma, aac) to load them in an array
     *      - Compare this array with the list of existing songs to keep only new tracks
     *      - Pass this array to the view.
     *
     * @see SongsController::ajax_import
     */
    public function import(){
        App::uses('Folder', 'Utility');

        $this->loadModel('Setting');

        $settings = $this->Setting->find('first');

        if ($settings) {
            $path = $settings['Setting']['rootpath'];
        } else {
            $path = false;
            $this->Session->setFlash(__('Please define a root path.'), 'flash_error');
            $this->redirect(array('controller' => 'settings', 'action' => 'index'));
        }

        $dir = new Folder($path);
        $songs = $dir->findRecursive('^.*\.(mp3|ogg|flac|wma|aac)$');
        $existingSongs = $this->Song->find('list', array('fields' => array('id', 'source_path')));
        $new = array_merge(array_diff($songs, $existingSongs));
        $deleted = array_diff($existingSongs, $songs);
        $this->set('songs',json_encode($new));
    }

    /**
     * The albums view function.
     * Find songs in the database, alphabetically and grouped by album.
     */
    public function albums(){
        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        $this->Paginator->settings = array(
            'Song' => array(
                'limit'         => 36,
                'fields'        => array('Song.band', 'Song.album', 'Song.cover'),
                'order'         => $this->Song->albumOrder,
                'group'         => 'Song.album'
            )
        );
        $songs = $this->Paginator->paginate();

        foreach ($songs as &$song) {
            $song['Song']['cover'] = empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover'];
        }

        if (empty($songs)) {
            $this->Session->setFlash('<strong>'.__('Oops!').'</strong> '.__('The database is empty...'), 'flash_info');
        }

        $this->set(compact('songs', 'playlists'));
    }

    /**
     * Get album content.
     * This function is called when you click on a cover from the albums view.
     */
    public function album(){
        $band = $this->request->query('band');
        $album = $this->request->query('album');
        $songs = $this->Song->find('all', array(
                'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.artist', 'Song.band', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc'),
                'conditions'    => array('Song.band' => $band, 'Song.album' => $album),
                'order'         => $this->Song->order
            )
        );

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
    public function artists(){
        $this->loadModel('Playlist');
        $this->Playlist->recursive = 0;
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        // Getting 5 band names.
        $this->Paginator->settings = array(
            'Song' => array(
                'limit'     => 5,
                'fields'    => array('Song.band'),
                'order'     => $this->Song->order,
                'group'     => array('Song.band')
            )
        );

        $bands = $this->Paginator->paginate();
        $band_list = array();
        foreach ($bands as $band) {
            $band_list[] = $band['Song']['band'];
        }

        // Finding the songs
        $songs = $this->Song->find('all', array(
            'fields'        => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.artist', 'Song.cover', 'Song.playtime', 'Song.track_number', 'Song.year', 'Song.disc', 'Song.genre'),
            'order'         => $this->Song->order,
            'conditions'    => array('Song.band' => $band_list)
        ));

        // Ordering the songs
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
                    'cover' => empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover'],
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
            $this->Session->setFlash("<strong>".__('Oops!')."</strong> ".__('The database is empty...'), 'flash_info');
        }
        $this->set(array('songs' => $parsed, 'playlists' => $playlists));
    }

    /**
     * The index view function
     * Get songs from database, ordered by artist.
     */
    public function index(){
        $this->Paginator->settings = array(
            'Song' => array(
                'limit'     => 50,
                'fields'    => array('Song.id', 'Song.title', 'Song.album', 'Song.band', 'Song.playtime', 'Song.track_number'),
                'order'     => $this->Song->order
            )
        );

        $songs = $this->Paginator->paginate();

        if (empty($songs)) {
            $this->Session->setFlash("<strong>".__('Oops!')."</strong> ".__('The database is empty...'), 'flash_info');
        }

        $this->loadModel('Playlist');
        $playlists = $this->Playlist->find('list', array(
            'fields'        => array('Playlist.id', 'Playlist.title'),
            'conditions'    => array('user_id' => AuthComponent::user('id'))
        ));

        $this->set(compact('songs', 'playlists'));
    }

    /**
     * Search view function
     * We just make a MySQL request...
     */
    public function search(){
        $query = isset($this->request->query['q']) ? $this->request->query['q'] : false ;

        if ($query) {
            $this->Paginator->settings = array(
                'Song' => array(
                    'limit'         => 5,
                    'fields'        => array('Song.band'),
                    'order'         => $this->Song->albumOrder,
                    'group'         => array('Song.band'),
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
                'order'         => $this->Song->order,
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
                        'cover' => empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover'],
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
                $this->Session->setFlash("<strong>".__('Oops!')."</strong> ".__('No results.'), 'flash_error');
            }
            $this->set('songs', $parsed);
        }
        $this->set('query', $query);
    }

    /**
     * This function is called by the player when you click on 'Play'
     * The file extension is checked to know if Sonerezh must converts the track.
     *
     * @param null $id
     * @return CakeResponse audio file
     */
    public function download($id = null){
        $this->loadModel('Setting');
        $settings = $this->Setting->find('first');

        $song = $this->Song->findById($id);
        if (!$song) {
            throw new NotFoundException();
        }

        if (empty($song['Song']['path'])) {
            $file_extension = substr(strrchr($song['Song']['source_path'], "."), 1);
        }else {
            $file_extension = substr(strrchr($song['Song']['path'], "."), 1);
        }

        if (empty($song['Song']['path']) || $file_extension != $settings['Setting']['convert_to']) {
            if (in_array($file_extension, explode(',', $settings['Setting']['convert_from']))) {
                $bitrate = $settings['Setting']['quality'];

                if ($settings['Setting']['convert_to'] == 'mp3') {
                    $path = TMP.date('YmdHis').".mp3";
                    $song['Song']['path'] = $path;
                    passthru('avconv -i "'.$song['Song']['source_path'].'" -threads 4  -c:a libmp3lame -b:a '.$bitrate.'k "'.$path.'"');
                } else if ($settings['Setting']['convert_to'] == 'ogg'){
                    $path = TMP.date('YmdHis').".ogg";
                    $song['Song']['path'] = $path;
                    passthru('avconv -i "'.$song['Song']['source_path'].'" -threads 4  -c:a libvorbis -b:a '.$bitrate.'k "'.$path.'"');
                }
            } else if(empty($song['Song']['path'])) {
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
        $song = $this->Song->find('first', array('conditions' => array('Song.id' => $id)));
        $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
        $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover']);
        $this->layout = false;
        $this->render(false);
        echo json_encode($song);
    }

    public function ajax_artist() {
        $artist = $this->request->query('artist');
        $songs = $this->Song->find('all', array(
            'conditions' => array(
                'Song.band' => $artist
            ),
            'order' => $this->Song->order
        ));
        foreach ($songs as &$song) {
            $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover']);
        }
        $this->layout = false;
        $this->render(false);
        echo json_encode($songs);
    }

    public function ajax_album() {
        $artist = $this->request->query('artist');
        $album = $this->request->query('album');
        $songs = $this->Song->find('all', array(
            'conditions' => array(
                'Song.band' => $artist,
                'Song.album' => $album
            ),
            'order' => $this->Song->albumOrder
        ));
        foreach ($songs as &$song) {
            $song['Song']['url'] = Router::url(array('controller'=>'songs', 'action'=>'download', $song['Song']['id'], 'api'=> false));
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover']);
        }
        $this->layout = false;
        $this->render(false);
        echo json_encode($songs);
    }

    public function ajax_playlist() {
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
            $song['Song']['cover'] = $this->request->base.DS.IMAGES_URL.(empty($song['Song']['cover']) ? "no-cover.png" : THUMBNAILS_DIR.$song['Song']['cover']);
        }
        $this->layout = false;
        $this->render(false);
        echo json_encode($songs);
    }

    # ------------------------- Sonerezh API ------------------------- #


}