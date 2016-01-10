<?php

App::uses("AppController", "Controller");

/**
 * Class SettingsController
 * Manage Sonerezh settings panel. Add the ability to clear the cache, or reset the entire song database.
 *
 * @property Setting $Setting
 */
class SettingsController extends AppController {

    public $helpers = array('FileSize');

    /**
     * This function manages the Sonerezh settings panel.
     * It also calculates some statistics and checks if avconv command is available.
     */
    public function index() {

        $this->loadModel('Song');
        $this->Setting->contain('Rootpath');

        if ($this->request->is(array('POST', 'PUT'))) {

            $rootpaths = array();
            foreach ($this->request->data['Rootpath'] as $rootpath) {
                if (isset($rootpath['id'])) {
                    $rootpaths[] = $rootpath['id'];
                }
            }
            $sessionRootpaths = $this->Session->check('rootpaths') ? $this->Session->read('rootpaths') : array();
            $deleteRootpaths = array_diff($sessionRootpaths, $rootpaths);

            if ($this->Setting->saveAssociated($this->request->data)) {

                $this->Setting->Rootpath->deleteAll(array('Rootpath.id' => $deleteRootpaths));
                $this->request->data = $this->Setting->find('first');
                $this->_saveRootpathsInSession($this->request->data['Rootpath']);
                $this->Session->setFlash(__('Settings saved !'), 'flash_success');

            } else {
                $this->Session->setFlash(__('Unable to save settings!'), 'flash_error');
            }
        }

        if (empty($this->request->data)) {
            $this->request->data = $this->Setting->find('first');
            $this->_saveRootpathsInSession($this->request->data['Rootpath']);
        }
        if (isset($this->request->data['Setting']['convert_from'])) {
            $convert_from = explode(',', $this->request->data['Setting']['convert_from']);
            foreach ($convert_from as $v) {
                $this->request->data['Setting']['from_'.$v] = true;
            }
        }

        $stats['artists'] = $this->Song->find('count', array(
            'group' => 'Song.artist'
        ));

        $stats['albums'] = $this->Song->find('count', array(
            'group' => 'Song.album, Song.band'
        ));

        $stats['songs'] = $this->Song->find('count', array(
            'group' => 'Song.title, Song.album, Song.band'
        ));

        // Thumbnails cache size
        $stats['thumbCache'] = 0;

        if (is_dir(RESIZED_DIR)) {
            $recursiveResizedDirectoryIterator = new RecursiveDirectoryIterator(RESIZED_DIR);
            $recursiveResizedIteratorIterator = new RecursiveIteratorIterator($recursiveResizedDirectoryIterator);

            foreach ($recursiveResizedIteratorIterator as $file) {
                $stats['thumbCache'] += $file->getSize();
            }
        }

        // Audio cache size
        $stats['audioCache'] = 0;
        $recursiveTmpDirectoryIterator = new RecursiveDirectoryIterator(TMP);
        $recursiveTmpIteratorIterator = new RecursiveIteratorIterator($recursiveTmpDirectoryIterator);
        $regexTmpIterator = new RegexIterator($recursiveTmpIteratorIterator, '/^.+\.(mp3|ogg)$/i');

        foreach ($regexTmpIterator as $audio_file) {
            if (!$audio_file->isLink()) {
                $stats['audioCache'] += $audio_file->getSize();
            }
        }

        // Check if avconv shell command is available
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $avconv = shell_exec("where avconv") || shell_exec("where ffmpeg");//WIN
        } else {
            $avconv = shell_exec("which avconv") || shell_exec("which ffmpeg");//NO WIN
        }

        $db = $this->Setting->getDataSource();
        $db_source = explode('/', $db->config['datasource']);
        $sonerezh_docker = ', ';
        if (DOCKER) {
            $sonerezh_docker = ' on Docker, ';
        }

        $stats['sonerezh_version'] = 'Sonerezh ' . SONEREZH_VERSION . $sonerezh_docker . $db_source[1];

        $this->set(compact('stats', 'avconv'));
    }

    /**
     * This function clears the Sonerezh caches.
     * It deletes all the .(mp3|ogg) files in tmp/ and the thumbnails cache.
     */
    public function clear() {
        App::uses('Folder', 'Utility');
        App::uses('File', 'Utility');
        $this->loadModel('Song');

        $dir = new Folder(TMP);
        $songs = $dir->findRecursive('^.*\.(mp3|ogg)$');
        foreach ($songs as $song) {
            $file = new File($song);
            $file->delete();
        }
        $dir = new Folder(RESIZED_DIR);
        $dir->delete();
        $this->Song->updateAll(array('path' => null));
        $this->Session->setFlash('<strong>Yeah! </strong>'.__('Cache cleared!'), 'flash_success');
        $this->redirect(array('controller' => 'settings', 'action' => 'index'));
    }

    /**
     * This function truncate the songs table and clear the Sonerezh cache.
     * Users data and playlist are preserved, but playlists are emptied.
     *
     * @see SettingsController::clear()
     */
    public function truncate() {
        try {
            App::uses('Folder', 'Utility');
            App::uses('File', 'Utility');
            $this->loadModel('Song');
            $this->loadModel('PlaylistMembership');

            $this->Song->deleteAll(array(null));
            $this->PlaylistMembership->deleteAll(array(null));

            $thumbnails_dir = new Folder(IMAGES.THUMBNAILS_DIR.DS);
            $resized_dir = new Folder(RESIZED_DIR);
            $tmp_dir = new Folder(TMP);
            $songs = $tmp_dir->findRecursive('^.*\.(mp3|ogg)$');

            $thumbnails_dir->delete();
            $resized_dir->delete();

            foreach ($songs as $song) {
                $file = new File($song);
                $file->delete();
            }

            $this->Session->setFlash(__('All entries have been deleted !'), 'flash_success');
            return $this->redirect(array('action' => 'index'));
        } catch (Exception $e) {
            $this->Session->setFlash(__('Unable to clean the database!'), 'flash_error');
            return $this->redirect(array('action' => 'index'));
        }
    }

    protected function _saveRootpathsInSession($rootpaths) {
        $rps = array();
        foreach ($rootpaths as $rootpath) {
            $rps[] = $rootpath['id'];
        }
        $this->Session->delete('rootpaths');
        $this->Session->write('rootpaths', $rps);
    }
}