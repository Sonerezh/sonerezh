<?php

class AudioFileScanner
{
    /**
     * Browses the filesystem to find audio files.
     *
     * @param bool $new         The function will search for audio files that have not been saved yet, or skip them
     *                          otherwise.
     * @param bool $orphans     The function will search for audio files that exist in the database, but not on the
     *                          filesystem, or skip them otherwise.
     * @param bool $outdated    The function will search for audio files that have been modified on the filesystem since
     *                          the last synchronization, or skip them otherwise.
     * @param int $batch        The maximum size of the response array. Unlimited if set to 0 or less.
     * @return array
     */
    public function scan($new = true, $orphans = false, $outdated = false, $batch = SYNC_BATCH_SIZE)
    {
        if ($batch <= 0) {
            $batch = false;
        }

        $data = array(
            'imported' => array(),
            'to_import' => array(),
            'to_update' => array(),
            'to_remove' => array()
        );
        $Track = ClassRegistry::init('Track');
        $Rootpath = ClassRegistry::init('Rootpath');
        $rootpaths = $Rootpath->find('list', array(
            'fields' => 'rootpath'
        ));

        if (empty($rootpaths)) {
            return $data;
        }

        $imported = $Track->find('all', array(
            'fields' => array('id', 'source_path', 'updated')
        ));

        $importedPaths = array();
        $importedUpdates = array();
        foreach ($imported as $value) {
            $importedPaths[$value['Track']['source_path']] = $value['Track']['id'];
            $importedUpdates[$value['Track']['id']] = $value['Track']['updated'];
        }

        App::uses('Folder', 'Utility');
        foreach ($rootpaths as $rootpath) {
            $folder = new Folder($rootpath);
            $tree = $folder->tree();

            foreach ($tree[0] as $directory) {
                $directory = new Folder($directory);

                // Skip symlinks to avoid infinite loops
                if (is_link($directory->path)) {
                    continue;
                }

                $foundInThisDirectory = $directory->find('^.*\.(mp3|ogg|flac|aac)$');
                if (count($foundInThisDirectory) == 0) {
                    continue;
                }

                foreach ($foundInThisDirectory as $key => $value) {
                    $suffix = Folder::isSlashTerm($directory->path) ? $value : DS . $value;
                    $sourcePath = $directory->path . $suffix;
                    $index = &$importedPaths[$sourcePath];

                    if ($index === null && $new) {
                        $data['to_import'][] = $sourcePath;
                    } elseif (filemtime($sourcePath) > strtotime($importedUpdates[$index]) && $outdated) {
                        $data['to_update'][] = $index;
                        $data['imported'][] = $sourcePath;
                    } else {
                        $data['imported'][] = $sourcePath;
                    }

                    $countToImport = count($data['to_import']);
                    $countToUpdate = count($data['to_update']);
                    if ($batch && ($countToImport == $batch || $countToUpdate == $batch)) {
                        break 3;
                    } else {
                        continue;
                    }
                }
            }
        }

        if ($orphans) {
            $data['to_remove'] = [];
            foreach ($importedPaths as $path => $id) {
                if (
                    !isset(array_flip($data['to_import'])[$path]) &&
                    !isset(array_flip($data['imported'])[$path])
                ) {
                    $data['to_remove'][] = $id;
                }
            }
        }

        return $data;
    }
}