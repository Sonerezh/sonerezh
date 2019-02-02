<?php

App::uses('AudioFileManager', 'AudioFileManager');
App::uses('AudioFileScanner', 'AudioFileScanner');

/**
 * The Sync Task handles synchronization sub-commands (import, update and
 * clean).
 */
class SyncTask extends AppShell
{
    protected $albumsBuffer = array();
    protected $bandsBuffer = array();
    public $uses = array('Album', 'Band', 'Track');

    /**
     * The sub-command entry-point.
     */
    public function execute()
    {
        $actions = null;
        if ($this->param('import')) {
            $actions[] = 'import';
        }

        if ($this->param('update')) {
            $actions[] = 'update';
        }

        if ($this->param('clean')) {
            $actions[] = 'clean';
        }

        if ($actions === null) {
            $actions = array('import','update','clean');
        }

        foreach ($actions as $action) {
            $this->$action();
        }
    }

    /**
     * Imports audio files metadata into Sonerezh's database.
     */
    public function import()
    {
        if (!$this->lock()) {
            return;
        }
        $res = array('errors' => false, 'imported' => array());

        $this->out('<info>[INFO   ]</info> Building the list of files to import.');
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = true, $orphans = false, $outdated = false, $batch = 0);

        if (empty($scan['to_import'])) {
            $this->out('<info>[INFO   ]</info> Scan result is empty, nothing to do.');
            $this->unlock();
            return;
        }

        if (!$this->isAgree(count($scan['to_import']))) {
            $this->out('<info>[INFO   ]</info> Ok. Bye.');
            $this->unlock();
            return;
        }

        $this->out('<info>[INFO   ]</info> Import in progress, please be patient...');
        $consoleWidth = exec('tput cols', $output, $return_var);
        if ($return_var !== 0) {
            $consoleWidth = 80;
        }

        // The purpose of the two variables below is to avoid useless calls to
        // the database to know if a band or an album already exists.
        $bands_buffer = array();
        $albums_buffer = array();

        $progress = $this->helper('progress');
        $progress->init(array(
            'total' => count($scan['to_import']),
            'width' => $consoleWidth
        ));

        foreach ($scan['to_import'] as $path) {
            $audio = new AudioFileManager($path);
            $result = $audio->parse();

            $metadata['Band'] = array('foo' => 'bar');

            if ($result['status'] != 0) {
                $this->log('-----', $scopes = 'synchronization');
                $this->log($path, $scopes = 'synchronization');
                $this->log($result['status_msg'], $scopes = 'synchronization');
                $res['errors'] = true;
                $progress->increment();
                $progress->draw();
                continue;
            } else {
                $metadata = $result['data'];
            }

            $band_id = &$bands_buffer[$metadata['Band']['name']];
            if ($band_id === null) {
                $band = $this->findOrSaveBand($metadata, $path);
                if ($band === false) {
                    $res['errors'] = true;
                    continue;
                } else {
                    $band_id = $band['Band']['id'];
                    $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                }
            }

            $metadata['Album']['band_id'] = $band_id;
            $album_id = &$albums_buffer[$metadata['Album']['name']];
            if ($album_id === null) {
                $album = $this->findOrSaveAlbum($metadata, $path);
                if ($album === false) {
                    $res['errors'] = true;
                    continue;
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
                $this->log('-----', $scopes = 'synchronization');
                $this->log($path, $scopes = 'synchronization');
                $this->log('Unable to save track.', $scopes = 'synchronization');
                $this->log('Record: ' . json_encode($metadata['Band']), $scopes = 'synchronization');
                $this->log('Validation error: ' . json_encode($this->Band->validationErrors), $scopes = 'synchronization');
                $res['errors'] = true;
            }

            $this->Album->clear();
            $this->Band->clear();
            $this->Track->clear();

            $progress->increment();
            $progress->draw();
        }

        $this->unlock();

        if ($res['errors'] === true) {
            $this->out('<error>[ERROR  ]</error> Errors happened during import. More information in the logs.');
            $this->out('<error>[ERROR  ]</error> See: ' . APP . 'tmp' . DS . 'logs' . DS . 'sonerezh-synchronization.log');
        } else {
            $this->out('<info>[INFO   ]</info> Database synchronization successful!');
        }
        $this->out('<info>[INFO   ]</info> Imported: ' . count($res['imported']) . ' file(s).');
    }

    /**
     * Updates records of the `tracks` table. They are edited because their
     * related file on the filesystem has been modified since the last import.
     */
    public function update()
    {
        if (!$this->lock()) {
            return;
        }
        $res = array('errors' => false, 'updated' => array());

        $this->out('<info>[INFO   ]</info> Building the list of files to update.');
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = false, $orphans = false, $outdated = true, $batch = 0);

        if (empty($scan['to_update'])) {
            $this->out('<info>[INFO   ]</info> Scan result is empty, nothing to do.');
            $this->unlock();
            return;
        }

        if (!$this->isAgree(count($scan['to_update']))) {
            $this->out('<info>[INFO   ]</info> Ok. Bye.');
            $this->unlock();
            return;
        }

        $this->out('<info>[INFO   ]</info> Update in progress, please be patient...');
        $consoleWidth = exec('tput cols', $output, $return_var);
        if ($return_var !== 0) {
            $consoleWidth = 80;
        }

        // The purpose of the two variables below is to avoid useless calls to
        // the database to know if a band or an album already exists.
        $bands_buffer = array();
        $albums_buffer = array();
        $original_tracks = $this->Track->find('all', array(
            'conditions' => array('id' => $scan['to_update'])
        ));

        $progress = $this->helper('progress');
        $progress->init(array(
            'total' => count($scan['to_update']),
            'width' => $consoleWidth
        ));

        foreach ($original_tracks as $original_track) {
            $audio = new AudioFileManager($original_track['Track']['source_path']);
            $result = $audio->parse();

            if ($result['status'] != 0) {
                $this->log('-----', $scopes = 'synchronization');
                $this->log($original_track['Track']['source_path'], $scopes = 'synchronization');
                $this->log($result['status_msg'], $scopes = 'synchronization');
                $res['errors'] = true;
                $progress->increment();
                $progress->draw();
                continue;
            } else {
                $metadata = $result['data'];
            }

            $band_id = &$bands_buffer[$metadata['Band']['name']];
            if ($band_id === null) {
                $band = $this->findOrSaveBand($metadata, $original_track['Track']['source_path']);
                if ($band === false) {
                    $res['errors'] = true;
                    continue;
                } else {
                    $bands_buffer[$band['Band']['name']] = $band['Band']['id'];
                    $band_id =  $band['Band']['id'];
                }
            }

            $metadata['Album']['band_id'] = $band_id;
            $album_id = &$albums_buffer[$metadata['Album']['name']];
            if ($album_id === null) {
                $album = $this->findOrSaveAlbum($metadata, $original_track['Track']['source_path']);
                if ($album === false) {
                    $res['errors'] = true;
                    continue;
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
                $this->log('-----', $scopes = 'synchronization');
                $this->log($original_track['Track']['source_path'], $scopes = 'synchronization');
                $this->log('Unable to save track.', $scopes = 'synchronization');
                $this->log('Record: ' . json_encode($metadata['Band']), $scopes = 'synchronization');
                $this->log('Validation error: ' . json_encode($this->Band->validationErrors), $scopes = 'synchronization');
                $res['errors'] = true;
            }

            $this->Album->clear();
            $this->Band->clear();
            $this->Track->clear();

            $progress->increment();
            $progress->draw();
        }

        $this->cleanOrphanDatabaseRecords();
        $this->unlock();

        if ($res['errors'] === true) {
            $this->out('<error>[ERROR  ]</error> Errors happened during update. More information in the logs.');
            $this->out('<error>[ERROR  ]</error> See: ' . APP . 'tmp' . DS . 'logs' . DS . 'sonerezh-synchronization.log');
        } else {
            $this->out('<info>[INFO   ]</info> Database update successful!');
        }
        $this->out('<info>[INFO   ]</info> Updated: ' . count($res['updated']) . ' record(s).');
    }

    /**
     * Removes orphan records from the database.
     * An orphan record exists in the database, but not on the filesystem.
     */
    public function clean()
    {
        if (!$this->lock()) {
            return;
        }

        $this->out('<info>[INFO   ]</info> Building the list of orphan records.');
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = true, $orphans = true, $outdated = false, $batch = 0);

        if (empty($scan['to_remove'])) {
            $this->out('<info>[INFO   ]</info> Scan result is empty, nothing to do.');
            $this->unlock();
            return;
        }

        if (!$this->isAgree(count($scan['to_remove']))) {
            $this->out('<info>[INFO   ]</info> Ok. Bye.');
            $this->unlock();
            return;
        }

        $this->out('<info>[INFO   ]</info> Deletion in progress, please be patient...');
        $this->Track->deleteAll(array('id' => $scan['to_remove']), false);
        $this->cleanOrphanDatabaseRecords();
        $this->unlock();
        $this->out('<info>[INFO   ]</info> Cleaning done!');
    }

    /**
     * Finds an Album from the database based on its name, or creates it if it
     * doesn't exist.
     */
    private function findOrSaveAlbum($metadata, $path)
    {
        $album = $this->Album->find('first', array(
            'fields' => array('id', 'name'),
            'conditions' => array('name' => $metadata['Album']['name'])
        ));

        if (empty($album)) {
            $this->Album->create();
            $album = $this->Album->save($metadata['Album']);
            if (empty($album)) {
                $this->log('-----', $scopes = 'synchronization');
                $this->log($path, $scopes = 'synchronization');
                $this->log('Unable to save extracted Album name.', $scopes = 'synchronization');
                $this->log('Record: ' . json_encode($metadata['Band']), $scopes = 'synchronization');
                $this->log('Validation error: ' . json_encode($this->Band->validationErrors), $scopes = 'synchronization');
                $album = false;
            }
        }
        return $album;
    }

    /**
     * Finds a Band from the database based on its name, or createss it if it
     * doesn't exist.
     */
    private function findOrSaveBand($metadata, $path)
    {
        $band = $this->Band->find('first', array(
            'fields' => array('id', 'name'),
            'conditions' => array('name' => $metadata['Band']['name'])
        ));

        if (empty($band)) {
            $this->Band->create();
            $band = $this->Band->save($metadata['Band']);
            if (empty($band)) {
                $this->log('-----', $scopes = 'synchronization');
                $this->log($path, $scopes = 'synchronization');
                $this->log('Unable to save extracted Band name. Skipping.', $scopes = 'synchronization');
                $this->log('Record: ' . json_encode($metadata['Band']), $scopes = 'synchronization');
                $this->log('Validation error: ' . json_encode($this->Band->validationErrors), $scopes = 'synchronization');
                $band = false;
            }
        }
        return $band;
    }

    /**
     * Asks the user if he/she is sure of what he/she is doing.
     *
     * @param int $count The number of files to being processed.
     * @return bool The answer of the user (Yes or No).
     */
    private function isAgree($count = 0)
    {
        $choice = $this->param('force') ? 'Y' : $this->in(
            sprintf('%d files to process. Continue?', $count),
            array('Y', 'N'),
            'Y'
        );

        return (bool)($choice == 'Y');
    }

    /**
     * Uses the CakePHP cache system to write a lock.
     */
    private function lock()
    {
        if (Cache::read('import')) {
            $this->out('<warning>[WARNING]</warning> The import process is already running via another client or the CLI.');
            return false;
        } else {
            Cache::write('import', true);
            $this->out('<info>[DEBUG  ]</info> Lock set.', 1, Shell::VERBOSE);
            return true;
        }
    }

    /**
     * Removes the lock from the CakePHP cache system.
     */
    private function unlock()
    {
        Cache::delete('import');
        $this->out('<info>[DEBUG  ]</info> Lock deleted.', 1, Shell::VERBOSE);
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

    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addOption('clean', array(
            'short' => 'c',
            'help' => 'Only perform cleaning process.',
            'boolean' => true,
        ))->addOption('import', array(
            'short' => 'i',
            'help' => 'Only perform import process.',
            'boolean' => true,
        ))->addOption('force', array(
            'short' => 'f',
            'help' => 'Disable interactions and answer "yes" to every command.',
            'boolean' => true,
            'default' => false,
        ))->addOption('update', array(
            'short' => 'u',
            'help' => 'Only perform update process.',
            'boolean' => true,
        ));

        return $parser;
    }
}