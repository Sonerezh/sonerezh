<?php

App::uses('AppController', 'Controller');

class TracksController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
    }

    public function tracks($id)
    {
        $this->viewClass = 'Json';
        $track = $this->Track->find('first', array(
            'conditions' => array('Track.id' => $id),
            'contain' => array('Album')
        ));

        if ($track['Album']['band_id']) {
            $this->loadModel('Band');
            $track['Band'] = $this->Band->findById($track['Album']['band_id']);
        }

        if (empty($track)) {
            throw new NotFoundException();
        }

        $this->set(compact('track'));
        $this->set('_serialize', 'track');
    }

    /**
     * This function is called by the player when you click on "Play".
     * The file extension is checked to know if a conversion must be triggered
     * or not.
     */
    public function download($id = null)
    {
        $this->loadModel('Setting');
        $settings = $this->Setting->find('first');

        $track = $this->Track->findById($id);
        if (!$track) {
            throw new NotFoundException();
        }

        $path = empty($track['Track']['path']) ? $track['Track']['source_path'] : $track['Track']['path'];
        $extension = substr(strrchr($path, '.'), 1);

        if (empty($track['Track']['path']) || $extension != $settings['Setting']['convert_to']) {
            if (in_array($extension, explode(',', $settings['Setting']['convert_from']))) {
                $bitrate = $settings['Setting']['quality'];
                if (shell_exec('which avconv' || shell_exec('where avconv'))) {
                    $cmd = array('avconv');
                } else {
                    $cmd = array('ffmpeg');
                }

                $origin_locale = setlocale(LC_ALL, 0);
                $origin_locale = str_replace(';', '&', $origin_locale);
                parse_str($origin_locale, $locale_array);
                setlocale(LC_CTYPE, 'C.UTF-8');

                $filepath = TMP . date('YmdHis');
                if ($settings['Setting']['convert_to'] == 'mp3') {
                    $bitrate .= 'k';
                    $codec = 'libmp3lame';
                    $codec_option = '-b:a';
                    $filepath .= '.mp3';
                } elseif ($settings['Setting']['convert_to'] == 'ogg') {
                    $codec = 'libvorbis';
                    $codec_option = '-q:a';
                    $filepath .= '.ogg';
                }

                array_push(
                    $cmd, '-i', escapeshellarg($track['Track']['source_path']), '-threads', '4', '-c:a', $codec,
                    $codec_option, escapeshellarg($bitrate), escapeshellarg($filepath), '2>&1'
                );
                passthru(implode(' ', $cmd));
                setlocale(LC_CTYPE, $locale_array['LC_TYPE']);
            } elseif (empty($track['Track']['path'])) {
                $track['Track']['path'] = $track['Track']['source_path'];
            }

            $this->Track->id = $id;
            $this->Track->save($track);
        }

        // Symlink filenames containing ".." to avoid CakePHP request error.
        if (strpos($track['Track']['path'], '..') !== false) {
            $symlink = TMP . md5($track['Track']['path']) . '.' . substr(strrchr($track['Track']['path'], '.'), 1);
            if (!file_exists($symlink)) {
                $symlink($track['Track']['path'], $symlink);
            }
            $track['Track']['path'] = $symlink;
        }

        $this->response->file($track['Track']['path'], array('download' => true));
        $this->response->cache('-1 minute', '+2 hours');
        return $this->response;
    }

    /**
     * Legacy function used by the frontend (Javascript). To refactor.
     */
    public function sync()
    {
        $this->viewClass = 'Json';
        $this->loadModel('Band');
        $bands = $this->Band->find('all', array(
            'fields' => array('Band.name'),
            'contain' => array(
                'Album' => array(
                    'fields' => array('Album.name', 'Album.cover'),
                    'order' => 'year',
                    'Track' => array(
                        'fields' => array(
                            'Track.id', 'Track.artist', 'Track.title',
                            'Track.disc_number', 'Track.disc_number',
                            'Track.track_number', 'Track.playtime'
                        ),
                        'conditions' => array('imported' => true),
                        'order' => array('Track.disc_number', 'Track.track_number')
                    ),
                )
            )
        ));

        $data = array();
        foreach ($bands as $band) {
            foreach ($band['Album'] as $album) {
                if (empty($album['cover'])) {
                    $cover = implode('/', array($this->request->base, IMAGES_URL, 'no-cover.png'));
                } else {
                    $cover = implode(
                        '/',
                        array($this->request->base, str_replace('/', '', IMAGES_URL), THUMBNAILS_DIR, $album['cover'])
                    );
                }

                foreach ($album['Track'] as $track) {
                    unset($track['album_id']);
                    $record = $track;
                    $record = array_merge($record, array(
                        'album' => $album['name'],
                        'band' => $band['Band']['name'],
                        'cover' => $cover,
                        'url' => implode(
                            '/',
                            array($this->request->base, 'tracks/download', $track['id'])
                        )
                    ));
                    $data[] = $record;
                }
            }
        }

        $this->set(compact('data'));
        $this->set('_serialize', 'data');
    }

    /**
     * A function to replace the legacy Javascript calls to the IndexedDB.
     */
    public function api_view($trackId)
    {
        $this->viewClass = 'Json';

        $raw = $this->Track->find('first', array(
            'fields' => array(
                'Track.id', 'Track.title', 'Track.playtime',
                'Track.track_number', 'Track.disc_number', 'Track.artist',
            ),
            'conditions' => array('imported' => true, 'Track.id' => $trackId),
            'order' => array(
                'Track.disc_number' => 'ASC',
                'Track.track_number' => 'ASC'
            ),
            'contain' => array(
                'Album' => array(
                    'fields' => array(
                        'Album.name', 'Album.cover', 'Album.band_id'
                    )
                )
            )
        ));

        if ($raw['Album']['band_id']) {
            $this->loadModel('Band');
            $raw = array_merge($raw, $this->Band->findById($raw['Album']['band_id']));
        }

        $data = array(
            'id' => $raw['Track']['id'],
            'title' => $raw['Track']['title'],
            'artist' => $raw['Track']['artist'],
            'band' => ($raw['Band']) ? $raw['Band']['name'] : null,
            'album' => $raw['Album']['name'],
            'cover' => $raw['Album']['cover'],
            'disc_number' => $raw['Track']['disc_number'],
            'track_number' => $raw['Track']['track_number'],
            'playtime' => $raw['Track']['playtime'],
            'url' => $this->request->base . '/tracks/download/' . $raw['Track']['id']
        );

        $this->set(compact('data'));
        $this->set('_serialize', 'data');
    }
}