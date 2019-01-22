<?php

/**
 * The Status task handles getting information about the synchronization status.
 */
class StatusTask extends AppShell
{
    public $uses = array('Track');

    public function execute ()
    {
        $this->cleanNotImportedTracks(); // Clean previous failed import
        $scanner = new AudioFileScanner();
        $scan = $scanner->scan($new = true, $orphans = true, $outdated = true, $batch = 0);

        $data = array(
            'to_import' => count($scan['to_import']),
            'to_update' => count($scan['to_update']),
            'to_remove' => count($scan['to_remove'])
        );

        if ($this->param('json')) {
            $this->out(json_encode($data));
        } else {
            foreach ($data as $k => $v) {
                $label = str_replace('_', ' ', ucfirst($k));
                $this->out("$label: $v file(s)");
            }
        }
    }

    private function cleanNotImportedTracks()
    {
        $this->Track->deleteAll(array('imported' => false));
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();

        $parser->addOption('json', array(
            'short' => 'j',
            'help' => 'Enable JSON output.',
            'boolean' => true
        ));

        return $parser;
    }
}