<?php

App::uses('AppShell', 'Console/Command');
App::uses('AudioFileManager', 'AudioFileManager');
App::uses('AudioFileScanner', 'AudioFileScanner');
App::uses('Folder', 'Utility');
App::uses('SongManager', 'SongManager');

App::import('Vendor', 'Getid3/getid3');

/**
 * The Sonerezh Shell provides several commands to manage and synchronize
 * the Sonerezh's database.
 */
class SonerezhShell extends AppShell
{

    public $tasks = array('Status', 'Sync');

    /**
     * Each sub-command has its own Cake Task.
     */
    public function getOptionParser() {
        $parser = parent::getOptionParser();

        $parser->addSubcommands(array(
            'status' => array(
                'help' => 'Return the synchronization status.',
                'parser' => $this->Status->getOptionParser()
            ),
            'sync' => array(
                'help' => 'Manages synchronization (import, update and cleaning).',
                'parser' => $this->Sync->getOptionParser()
            )
        ));

        return $parser;
    }

    /**
     * Override the _welcome parent method to remove the default CakePHP shell
     * header.
     */
    protected function _welcome()
    {
    }
}
