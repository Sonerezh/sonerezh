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
    public $components = array('CheckCmd');

    /**
     * This function manages the Sonerezh settings panel.
     * It also calculates some statistics and checks if avconv command is available.
     */
    public function index(){

        $this->loadModel('Song');

        if ($this->request->is(array('POST', 'PUT'))) {
            if ($this->Setting->save($this->request->data)) {
                $this->Session->setFlash(__('Settings saved !'), 'flash_success');
            } else {
                $this->Session->setFlash(__('Unable to save settings!'), 'flash_error');
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
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(RESIZED_DIR)) as $file) {
                $stats['thumbCache'] += $file->getSize();
            }
        }

        // MP3 cache size
        $stats['mp3Cache'] = 0;
        foreach (new RegexIterator(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(TMP)), '/^.+\.(mp3|ogg)$/i') as $mp3) {
            $stats['mp3Cache'] += $mp3->getSize();
        }

        // Check if avconv shell command is available
        $cmd = $this->CheckCmd->is_shell_exec_available('which avconv');
        $avconv = empty($cmd) ? false : true;

        if (empty($this->request->data)) {
            $this->request->data = $this->Setting->find('first');
            $convert_from = explode(',', $this->request->data['Setting']['convert_from']);

            foreach ($convert_from as $v) {
                $this->request->data['Setting']['from_'.$v] = true;
            }
        }

        $this->set(array('stats' => $stats, 'avconv' => $avconv));
    }

    /**
     * This function clears the Sonerezh caches.
     * It deletes all the .(mp3|ogg) files in tmp/ and the thumbnails cache.
     */
    public function clear(){
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
}