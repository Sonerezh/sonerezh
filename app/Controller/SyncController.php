<?php

App::uses('AppController', 'Controller');

class SyncController extends AppController
{
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Security->csrfCheck = false;
        $this->Security->validatePost = false;
    }

    /**
     * Handles authorization check. Only admin users can access this controller.
     * @param $user
     * @return bool
     */
    public function isAuthorized($user)
    {
        return (bool)($user['role'] === 'admin');
    }

    /**
     * Get statistics about the synchronization between the Sonerezh's database
     * and the filesystem.
     */
    public function index()
    {
        if ($this->request->is('ajax') && $this->request->header('X-Powered-By') == 'Axios') {
            $this->viewClass = 'Json';
            $this->cleanNotImportedTracks(); // Clean all the previous failed imports

            App::uses('AudioFileScanner', 'AudioFileScanner');
            $scanner = new AudioFileScanner();
            $scan = $scanner->scan($new = true, $orphans = true, $outdated = true, $batch = 0);

            $data = array(
                'to_import' => count($scan['to_import']),
                'to_update' => count($scan['to_update']),
                'to_remove' => count($scan['to_remove'])
            );

            $this->set(compact('data'));
            $this->set('_serialize', 'data');
        }
    }

    /**
     * Updates records of the `tracks` table. They are edited because their
     * related file on the filesystem has been modified since the last import.
     * Up to 250 files are processed per request.
     */
    public function patchSync()
    {
        $this->viewClass = 'Json';
        $res = array();

        if (Cache::read('import')) {
            $this->response->statusCode(503);
            $res['errors'][] = __('The import process is already running via another client or the CLI.');
            $this->set(compact('res'));
            $this->set('_serialize', 'res');
            return;
        } else {
            Cache::Write('import', true);
        }

        App::uses('AudioFileScanner', 'AudioFileScanner');
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = false, $orphans = false, $outdated = true);

        if (empty($scan['to_update'])) {
            $this->response->statusCode(204);
            $this->set(compact('res'));
            $this->set('_serialize', 'data');
            Cache::delete('import');
            return;
        }

        App::uses('AudioFileManager', 'AudioFileManager');
        $this->loadModel('Album');
        $this->loadModel('Band');
        $this->loadModel('Track');

        // The purpose of the two variables below is to avoid useless calls to
        // the database to know if a band or an album already exists.
        $bands_buffer = array();
        $albums_buffer = array();
        $original_tracks = $this->Track->find('all', array(
            'conditions' => array('id' => $scan['to_update'])
        ));

        foreach ($original_tracks as $original_track) {
            $audio = new AudioFileManager($original_track['Track']['source_path']);
            $result = $audio->parse();

            if ($result['status'] != 0) {
                $res['errors'][] = $result['status_msg'];
                continue;
            } else {
                $metadata = $result['data'];
            }

            $band_id = &$bands_buffer[$metadata['Band']['name']];
            if ($band_id === null) {
                $band = $this->Band->find('first', array(
                    'fields' => array('id', 'name'),
                    'conditions' => array('name' => $metadata['Band']['name'])
                ));

                if (empty($band)) {
                    $this->Band->create();
                    $band = $this->Band->save($metadata['Band']);
                    if (!empty($band)) {
                        $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                        $band_id = $band['Band']['id'];
                    } else {
                        $res['errors'][] = array(
                            'error' => __('Unable to save band "%s".', h($metadata['Band']['name'])),
                            'record' => $metadata['Band'],
                            'validation_errors' => $this->Band->validationErrors
                        );
                    }
                } else {
                    $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                    $band_id =  $band['Band']['id'];
                }
            }

            $metadata['Album']['band_id'] = $band_id;
            $album_id = &$albums_buffer[$metadata['Album']['name']];
            if ($album_id === null) {
                $album = $this->Album->find('first', array(
                    'fields' => array('id', 'name'),
                    'conditions' => array('name' => $metadata['Album']['name'])
                ));

                if (empty($album)) {
                    $this->Album->create();
                    $album = $this->Album->save($metadata['Album']);
                    if (!empty($album)) {
                        $albums_buffer[$album['Album']['name']] = $album['Album']['id'];
                        $album_id = $album['Album']['id'];
                    } else {
                        $res['errors'][] = array(
                            'error' => __('Unable to save album "%s".', h($metadata['Album']['name'])),
                            'record' => $metadata['Album'],
                            'validation_errors' => $this->Album->validationErrors
                        );
                    }
                } else {
                    $albums_buffer[$album['Album']['name']] = $album['Album']['id'];
                    $album_id = $album['Album']['id'];
                }
            }

            $metadata['Track']['album_id'] = $album_id;
            $metadata['Track'] = array_merge($original_track['Track'], $metadata['Track']);
            $this->Track->create();
            if ($this->Track->save($metadata['Track'])){
                $res['updated'][] = $metadata['Track']['title'];
            } else {
                // The path is recorded into the database even if the first
                // attempt failed. But it is marked as "not imported".
                $this->Track->save(array(
                    'imported' => false,
                    'source_path' => $metadata['Track']['source_path']
                ));
                $res['errors'][] = array(
                    'error' => __('Unable to save track "%s".', h($metadata['Track']['source_path'])),
                    'record' => $metadata['Track'],
                    'validation_errors' => $this->Track->validationErrors
                );
            }

            $this->Album->clear();
            $this->Band->clear();
            $this->Track->clear();
        }

        Cache::delete('import');
        $this->set(compact('res'));
        $this->set('_serialize', 'res');
    }

    /**
     * Imports audio files metadata into Sonerezh's database.
     * Up to <SYNC_BATCH_SIZE> (default: 50) files are processed per request.
     */
    public function postSync()
    {
        $this->viewClass = 'Json';
        $res = array();

        if (Cache::read('import')) {
            $this->response->statusCode(503);
            $res['errors'][] = __('The import process is already running via another client or the CLI.');
            $this->set(compact('res'));
            $this->set('_serialize', 'res');
            return;
        } else {
            Cache::Write('import', true);
        }

        App::uses('AudioFileScanner', 'AudioFileScanner');
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = true, $orphans = false, $outdated = false);

        if (empty($scan['to_import'])) {
            $this->response->statusCode(204);
            $this->set(compact('res'));
            $this->set('_serialize', 'data');
            Cache::delete('import');
            return;
        }

        App::uses('AudioFileManager', 'AudioFileManager');
        $this->loadModel('Album');
        $this->loadModel('Band');
        $this->loadModel('Track');

        // The purpose of the two variables below is to avoid useless calls to
        // the database to know if a band or an album already exists.
        $bands_buffer = array();
        $albums_buffer = array();

        foreach ($scan['to_import'] as $path) {
            $audio = new AudioFileManager($path);
            $result = $audio->parse();

            if ($result['status'] != 0) {
                // The path is recorded into the database even if the parsing
                // attempt failed, to avoid infinite import loop. But it is
                // marked as "not imported".
                $this->Track->save(array(
                    'imported' => false,
                    'source_path' => $path
                ));
                $res['errors'][$path] = $result['status_msg'];
                $this->Track->clear();
                continue;
            } else {
                $metadata = $result['data'];
            }

            $band_id = &$bands_buffer[$metadata['Band']['name']];
            if ($band_id === null) {
                $band = $this->Band->find('first', array(
                    'fields' => array('id', 'name'),
                    'conditions' => array('name' => $metadata['Band']['name'])
                ));

                if (empty($band)) {
                    $this->Band->create();
                    $band = $this->Band->save($metadata['Band']);
                    if (!empty($band)) {
                        $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                        $band_id = $band['Band']['id'];
                    } else {
                        $res['errors'][] = array(
                            'error' => __('Unable to save band "%s".', h($metadata['Band']['name'])),
                            'record' => $metadata['Band'],
                            'validation_errors' => $this->Band->validationErrors,
                        );
                    }
                } else {
                    $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                    $band_id = $band['Band']['id'];
                }
            }

            $metadata['Album']['band_id'] = $band_id;

            $album_id = &$albums_buffer[$metadata['Album']['name']];
            if ($album_id === null) {
                $album = $this->Album->find('first', array(
                    'fields' => array('id', 'name'),
                    'conditions' => array('name' => $metadata['Album']['name'])
                ));

                if (empty($album)) {
                    $this->Album->create();
                    $album = $this->Album->save($metadata['Album']);
                    if (!empty($album)) {
                        $albums_buffer[$album['Album']['name']] = $album['Album']['id'];
                        $album_id = $album['Album']['id'];
                    } else {
                        $res['errors'][] = array(
                            'error' => __('Unable to save album "%s".', h($metadata['Album']['name'])),
                            'record' => $metadata['Album'],
                            'validation_errors' => $this->Album->validationErrors
                        );
                    }
                } else {
                    $albums_buffer[$album['Album']['name']] = $album['Album']['id'];
                    $album_id = $album['Album']['id'];
                }
            }

            $metadata['Track']['album_id'] = $album_id;
            $this->Track->create();
            if ($this->Track->save($metadata['Track'])){
                $res['imported'][] = $metadata['Track']['title'];
            } else {
                // The path is recorded into the database even if the first
                // attempt failed. But it is marked as "not imported".
                $this->Track->save(array(
                    'imported' => false,
                    'source_path' => $metadata['Track']['source_path']
                ));
                $res['errors'][] = array(
                    'error' => __('Unable to save track "%s".', h($metadata['Track']['source_path'])),
                    'record' => $metadata['Track'],
                    'validation_errors' => $this->Track->validationErrors
                );
            }

            $this->Album->clear();
            $this->Band->clear();
            $this->Track->clear();
        }

        Cache::delete('import');
        $this->response->statusCode(201);
        $this->set(compact('res'));
        $this->set('_serialize', 'res');
    }

    public function deleteSync()
    {
        $this->viewClass = 'Json';
        $res = array();

        if (Cache::read('import')) {
            $this->response->statusCode(503);
            $res['errors'][] = __('The import process is already running via another client or the CLI.');
            $this->set(compact('res'));
            $this->set('_serialize', 'res');
            return;
        } else {
            Cache::Write('import', true);
        }

        $this->loadModel('Track');
        try {
            App::uses('AudioFileScanner', 'AudioFileScanner');
            $scanner = new AudioFileScanner();
            $scan = $scanner->scan($new = true, $orphans = true, $outdated = false);
        } catch (Exception $exception) {
            $res['errors'][] = array(
                'error' => $exception->getMessage()
            );
            return;
        }

        if (empty($scan['to_remove'])) {
            $this->cleanOrphanDatabaseRecords();
            $this->response->statusCode(204);
            $this->set(compact('res'));
            $this->set('_serialize', 'data');
            Cache::delete('import');
            return;
        }

        $this->loadModel('Track');
        $deletion = $this->Track->deleteAll(array(
            'id' => $scan['to_remove']
        ), false);

        if (! $deletion) {
            $res['errors'][] = array(
                'error' => __('Unexpected error occurred while deleting data.')
            );
        } else {
            $this->cleanOrphanDatabaseRecords();
        }

        Cache::delete('import');
        $this->response->statusCode(202);
        $this->set(compact('res'));
        $this->set('_serialize', 'res');
    }

    /**
     * Deletes all database records marked as failed.
     */
    private function cleanNotImportedTracks()
    {
        $this->loadModel('Track');
        $this->Track->deleteAll(array('imported' => false));
    }

    /**
     * Cleans records from the `albums` and the `bands` tables which have not
     * any child in the database.
     */
    private function cleanOrphanDatabaseRecords()
    {
        $this->loadModel('Track');
        $db = $this->Track->getDataSource();
        if ($db->config['datasource'] == 'Database/Mysql') {
            $queries = array(
                'DELETE FROM albums LEFT JOIN tracks ON albums.id = tracks.album_id WHERE tracks.album_id IS NULL',
                'DELETE FROM bands LEFT JOIN albums ON bands.id = albums.band_id WHERE albums.band_id IS NULL'
            );
        } else {$this->loadModel('Track');
            $queries = array(
                'DELETE FROM albums WHERE NOT EXISTS (SELECT 1 FROM tracks WHERE tracks.album_id = albums.id)',
                'DELETE FROM bands WHERE NOT EXISTS (SELECT 1 FROM albums WHERE albums.band_id = bands.id)'
            );
        }

        foreach ($queries as $query) {
            $db->fetchAll($query);
        }
    }
}