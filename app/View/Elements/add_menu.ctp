<span class="glyphicon glyphicon-play song-controls action-play" title="<?php echo __('Play'); ?>"></span><span class="glyphicon glyphicon-pause song-controls action-pause" data-action="pause" title="<?php echo __('Pause'); ?>"></span><span class="dropdown"><span class="glyphicon glyphicon-plus song-controls action-add-to-playlist" data-toggle="dropdown" title="<?php echo __('Other actions'); ?>"></span>
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-play-next"><?php echo __('Play Next'); ?></a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-to-up-next"><?php echo __('Add to Up Next'); ?></a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-to" data-toggle="modal" data-type="song" data-target="#add-to" ><?php echo __('Add to...'); ?></a></li>
        <li role="presentation">
            <?php echo $this->Html->link(
                __('Download'),
                array('controller' => 'songs', 'action' => 'download', $song_id),
                array('class' => 'no-ajax', 'role' => 'menuitem', 'tabindex' => '-1', 'download' => $song_title));
            ?>
        </li>
    </ul>
</span>