<span class="glyphicon glyphicon-play song-controls action-play" title="<?= __('Play'); ?>"></span><span class="glyphicon glyphicon-pause song-controls action-pause" data-action="pause" title="<?= __('Pause'); ?>"></span><span class="dropdown"><span class="glyphicon glyphicon-plus song-controls action-add-to-playlist" data-toggle="dropdown" title="<?= __('Add to playlist'); ?>"></span>
    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu1">
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-play-next"><?= __('Play Next'); ?></a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-to-up-next"><?= __('Add to Up Next'); ?></a></li>
        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-to" data-toggle="modal" data-type="song" data-target="#add-to" ><?= __('Add to...'); ?></a></li>
    </ul>
</span>