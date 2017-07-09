<?php

App::uses('AppShell', 'Console/Command');
App::uses('Folder', 'Utility');
App::uses('SongManager', 'SongManager');

App::import('Vendor', 'Getid3/getid3');

class SonerezhShell extends AppShell {

    public $uses = array('Setting', 'Song');

    public function getOptionParser() {
        $parser = parent::getOptionParser();

        $parser->addArgument('path', array(
            'help' => 'The absolute path to the file or the folder you want to import.',
            'required' => true
        ));

        $parser->addOption('recursive', array(
            'short' => 'r',
            'help' => 'Import a folder, recursively.',
            'boolean' => true
        ))->command('import');

        return $parser;
    }

    public function import() {
        $path = $this->args[0];
        $recursive = $this->param('recursive');
        //$verbose = $this->param('verbose');

        if (Cache::read('import')) {
            $this->out("<warning>[WARN]</warning> The import process is already running via another client or the CLI. You can click on \"Clear cache\" on the settings page to remove the lock, if needed.");
            exit(0);
        }

        $found = array();

        if (is_dir($path)) {
            $path = new Folder($path);
            $this->out("<info>[INFO]</info> Scan $path->path...");

            if ($recursive) {
                $found = $path->findRecursive('^.*\.(mp3|ogg|flac|aac)$');
            } else {
                $found = $path->find('^.*\.(mp3|ogg|flac|aac)$');
                // The Folder::find() method does not return the absolute path of each file, we need to add it:
                $found = preg_filter('/^/', $path->path, $found);
            }

        } elseif (file_exists($path)) {
            $found = array($path);

        } else {
            $this->error('Invalid path');
        }

        $already_imported = $this->Song->find('list', array(
            'fields' => array('Song.id', 'Song.source_path')
        ));

        $to_import = array_merge(array_diff($found, $already_imported));
        $to_import_count = count($to_import);
        $found_count = count($found);

        if ($to_import_count == 1) {
            $selection = $this->in("[INFO] You asked to import $to_import[0]. Continue?", array(
                'yes',
                'no'
            ), 'yes');

        } elseif ($to_import_count > 1) {
            $diff = $found_count - $to_import_count;
            $selection = $this->in("[INFO] Found $to_import_count audio files ($diff already in the database). Continue?", array(
                'yes',
                'no'
            ), 'yes');

        } elseif ($found_count > 0 && $to_import_count == 0) {
            $this->out("<info>[INFO]</info> $found_count file(s) found, but already in the database.");
            exit(0);

        } else {
            $this->out('<info>[INFO]</info> Nothing to do.');
            exit(0);
        }

        if ($selection == 'no') {
            $this->out('<info>[INFO]</info> Ok, bye.');
            exit(0);
        }

        $this->out('<info>[INFO]</info> Run import', 0);

        // Write lock to avoid multiple import processes in the same time
        if (Cache::read('import')) {
            $this->out("<warning>[WARN]</warning> The import process is already running via another client or the CLI. You can click on \"Clear cache\" on the settings page to remove the lock, if needed.");
            exit(0);
        } else {
            Cache::write('import', true);

            // Catch SIGINT
            pcntl_signal(SIGINT, function() {
                Cache::delete('import');
                $this->refreshSyncToken();
                exit();
            });

            $i = 1;
            foreach ($to_import as $file) {

                pcntl_signal_dispatch();
                $song_manager = new SongManager($file);
                $parse_result = $song_manager->parseMetadata();

                if ($parse_result['status'] != 'OK') {
                    if ($parse_result['status'] == 'WARN') {
                        $this->overwrite("<warning>[WARN]</warning>[$file] - " . $parse_result['message']);
                    } elseif ($parse_result['status'] == 'ERR') {
                        $this->overwrite("<error>[ERR]</error>[$file] - " . $parse_result['message']);
                    }
                }

                $this->Song->create();
                $status = false;
                $message = "<error>[ERR]</error>[$file] - Unable to save the song metadata to the database";
                try {
                    $status = $this->Song->save($parse_result['data']);
                }
                catch (\Exception $e) {
                    $message = $e->getMessage();
                }
                if (!$status) {
                    $this->overwrite($message);
                }

                // Progressbar
                $percent_done = 100 * $i / $to_import_count;
                $hashtags_quantity = round(45 * $percent_done / 100);
                $remaining_spaces = 45 - $hashtags_quantity;

                if ($i < ($to_import_count)) {
                    $this->overwrite('<info>[INFO]</info> Run import: [' . round($percent_done) . '%] [' . str_repeat('#', $hashtags_quantity) . str_repeat(' ', $remaining_spaces) . ']', 0);
                } else {
                    $this->overwrite('<info>[INFO]</info> Run import: [' . round($percent_done) . '%] [' . str_repeat('#', $hashtags_quantity) . str_repeat(' ', $remaining_spaces) . ']');
                }

                $i++;
            }

            // Delete lock
            Cache::delete('import');
            $this->refreshSyncToken();
        }
    }

    public function refreshSyncToken() {
        // Update the sync_token to refresh the IndexedDB on the browser side
        $settings = $this->Setting->find('first');
        $settings['Setting']['sync_token'] = time();
        $this->Setting->save($settings);
    }
}
