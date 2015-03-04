<div class="col-xs-3">
    <div class="panel panel-default" style="margin-top: 20px;">
        <div class="panel-heading">
            <h4 class="panel-title"><?php echo __('Playlists'); ?></h4>
            <?php echo $this->Html->link(
                '<i class="glyphicon glyphicon-plus"></i>',
                '#',
                array(
                    'class' => 'btn btn-xs btn-primary btn-add-plst pull-right ',
                    'data-toggle' => 'modal',
                    'data-target' => '#add-playlist-modal',
                    'escape' => false
                )
            ); ?>
        </div>
        <ul class="list-group">
            <?php if(empty($playlists)): ?>
            <li class="list-group-item">&nbsp;</li>
            <?php endif; ?>
            <?php foreach($playlists as $id =>$p): ?>
            <li class="list-group-item ">
                <small><i class="glyphicon glyphicon-list"></i></small>&nbsp;
                <?php echo $this->Html->link(
                    $p,
                    array('controller' => 'playlists', 'action' => 'index', $id)
                ); ?>
                <?php echo $this->Html->link(
                    '<i class="glyphicon glyphicon-edit"></i>',
                    '#',
                    array(
                        'class' => 'btn btn-xs btn-info btn-mgmt btn-playlist-edit',
                        'data-toggle' => 'modal',
                        'data-target' => '#edit-playlist-'.$id.'-modal',
                        'title' => __('Rename'),
                        'escape' => false
                    )
                ); ?>
                <?php echo $this->Form->postLink(
                    '<i class="glyphicon glyphicon-trash"></i>',
                    array('controller' => 'playlists', 'action' => 'delete', $id),
                    array('class' => 'btn btn-xs btn-danger btn-mgmt btn-playlist-delete', 'escape' => false),
                    __('Are you sure ?')
                ); ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<div class="col-xs-9" style="padding-top: 20px;">
    <?php if(empty($playlists)): ?>
        <div class="alert alert-info">
            <?php echo __("You don't have playlists yet."); ?>
        </div>
    <?php else: ?>
        <div class="row playlist-row" data-playlist="<?php echo $playlistInfo['id'];?>">
            <div class="col-xs-10">
                <h4>
                    <?php echo h($playlistInfo['name']); ?>
                </h4>
            </div>
            <div class="col-xs-2">
                <h4 class="text-right">
                    <small>
                        <span class="glyphicon glyphicon-play song-controls action-play-playlist" title="<?= ('Play all albums'); ?>"></span>
                        <span class="glyphicon glyphicon-random song-controls action-shuffle-playlist" title="<?= ('Shuffle this artist'); ?>"></span>
                        <span class="dropdown">
                        <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?= ('Add to playlist'); ?>"></span>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-playlist-play-next"><?= __('Play Next'); ?></a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-playlist-to-up-next"><?= __('Add to Up Next'); ?></a></li>
                            </ul>
                        </span>
                    </small>
                </h4>
            </div>
            <hr style="clear: both;"/>
            <?php if(empty($playlist)): ?>
                <div class="alert alert-info">
                    <?php echo __("This playlist is empty :("); ?>
                </div>
            <?php else: ?>
                <table class="table table-striped table-condensed table-hover" data-scroll-container="true" style="margin-top: 20px;">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo __('Song Title'); ?></th>
                        <th class="hidden-xs hidden-sm"><?php echo __('Artist'); ?></th>
                        <th class="visible-lg"><?php echo __('Album'); ?></th>
                        <th class="text-right"><?php echo __('Duration'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($playlist as $song): ?>
                        <tr data-id="<?php echo h($song['Song']['id']); ?>" data-scroll-content="true">
                            <td class="track-number">
                                <span class="song-number"><?php echo h($song['PlaylistMembership']['sort']); ?></span>
                            </td>
                            <td class="truncated-name"><?php echo h($song['Song']['title']); ?></td>
                            <td class="truncated-name hidden-xs hidden-sm"><?php echo h($song['Song']['artist']); ?></td>
                            <td class="truncated-name visible-lg"><?php echo h($song['Song']['album']); ?></td>
                            <td class="text-right playtime-cell">
                                <span class="song-playtime"><?php echo h($song['Song']['playtime']); ?></span>
                                <span class="glyphicon glyphicon-play song-controls action-play" data-action="play" title="<?php echo __('Play'); ?>"></span><span class="glyphicon glyphicon-pause song-controls action-pause" data-action="pause" title="<?php echo __('Pause'); ?>"></span>
                                <?php echo $this->Form->postLink(
                                        '<span class="glyphicon glyphicon-remove song-controls action-remove-from-playlist" title="'.__("Remove").'"></span>',
                                        array('controller' => 'playlist_memberships', 'action' => 'remove', $song['PlaylistMembership']['id']),
                                        array('style' => 'text-decoration: none;', 'escape' => false),
                                        __('Remove').' '.h($song['Song']['title']).' '.__('from the playlist?')
                                ); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add playlist modal -->
<div id="add-playlist-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="addPlaylistModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo $this->Form->create('Playlist', array('action' => 'add')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php echo __('Create a playlist'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo $this->Form->input('title', array('placeholder' => __('Title'))); ?>
            </div>
            <div class="modal-footer">
                <?php echo $this->Form->submit(__('Create'), array('class' => 'btn btn-success')); ?>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </div>
</div>

<!-- Edit playlist modal -->
<?php if(!empty($playlists)): ?>
    <?php foreach($playlists as $id => $name): ?>
        <div id="<?php echo 'edit-playlist-'.$id.'-modal'; ?>" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editPlaylistModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <?php echo $this->Form->create('Playlist', array('action' => 'edit/'.$id)); ?>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo __('Rename a playlist'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo $this->Form->input('title', array('placeholder' => __('New title'), 'value' => $name)); ?>
                    </div>
                    <div class="modal-footer">
                        <?php echo $this->Form->submit(__('Save'), array('class' => 'btn btn-success')); ?>
                    </div>
                    <?php echo $this->Form->end(); ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>