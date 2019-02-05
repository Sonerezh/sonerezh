<div id="album-expended" data-band="<?php echo h($album['Band']['name']); ?>" data-album="<?php echo h($album['Album']['name']); ?>">
    <div class="container">
        <div class="row album" style="margin-bottom: 20px;">
            <div class="col-xs-12">
                <h3><?php echo h($album['Album']['name']); ?>
                    <small>
                        <span class="glyphicon glyphicon-play song-controls action-play-album" title="<?php echo __('Play album'); ?>"></span>
                        <span class="glyphicon glyphicon-random song-controls action-shuffle-album" title="<?php echo __('Shuffle this album'); ?>"></span>
                        <span class="dropdown">
                            <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?php echo __('Other actions'); ?>"></span>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-album-play-next"><?php echo __('Play Next'); ?></a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-album-to-up-next"><?php echo __('Add to Up Next'); ?></a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-toggle="modal" data-type="album" data-target="#add-to" ><?php echo __('Add to...'); ?></a></li>
                            </ul>
                        </span>
                    </small>
                    <span class="pull-right close-album">&times;</span>
                </h3>
                <?php if (isset($songs[1][0]['Song']['year'])) {
                    echo '<h4>'.h($album['Band']['name']).'&nbsp;&bull;&nbsp;'.h($album['Album']['year']).'</h4>';
                } else {
                    echo '<h4>'.h($album['Band']['name']).'</h4>';
                } ?>
            </div>
            <div class="clearfix"></div>
            <?php $discs_count = count($album['discs']); ?>
            <?php foreach ($album['discs'] as $d => $disc): ?>
                <?php if ($discs_count > 1): ?>
                <div class="col-xs-12">
                    <p class="disc-number">
                        <strong><?php echo __('Disc') . ' ' . h($d); ?></strong>
                    </p>
                </div>
                <?php endif; ?>
                <div class="col-lg-6 col-xs-12 album-table-container">
                    <table class="table table-hover table-album table-album-left">
                        <tbody>
                        <?php $countDiscTracks = count($disc['Track']); ?>
                        <?php $switch = 0; ?>
                        <?php $switchOn = $countDiscTracks > 5 ? round($countDiscTracks / 2) : $countDiscTracks; ?>
                        <?php foreach ($disc['Track'] as $t => $track): ?>
                            <?php if ($switch == $switchOn): ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6 col-xs-12 album-table-container">
                    <table class="table table-hover table-album">
                        <tbody>
                            <?php endif; ?>
                        <!-- Add 'class="on-air"' on play to highlight the row -->
                        <tr data-id="<?php echo h($track['id']); ?>">
                            <td class="track-number"><?php echo h($track['track_number']); ?></td>
                            <td class="truncated-name title">
                                <?php echo h($track['title']); ?>
                                <?php if ($track['artist'] != $album['Band']['name']): ?>
                                <br /><small><?php echo h($track['artist']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-right playtime-cell">
                                <span class="song-playtime"><?php echo h($track['playtime']); ?></span>
                                <?php echo $this->element('add_menu', array('id' => h($track['id']), 'title' => h($track['title']))); ?>
                            </td>
                        </tr>
                        <?php $switch += 1; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>