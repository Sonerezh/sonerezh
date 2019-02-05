<?php foreach ($bands as $band): ?>
	<!-- Artists view -->
	<div class="col-xs-12 band-name" data-band="<?php echo h($band['Band']['name']); ?>" data-scroll-content="true">
        <div class="col-xs-10">
		<h3>
            <?php echo h($band['Band']['name']); ?>
        </h3>
		<p class="band-stats">
            <small>
                <?php echo __n("%s Album", "%s Albums", count($band['Album']), count($band['Album'])).', '; ?>
                <?php echo __n("%s Track", "%s Tracks", $band['Band']['tracks_count'], $band['Band']['tracks_count']); ?>
            </small>
        </p>
        </div>
        <div class="col-xs-2">
            <h3 class="text-right">
                <small>
                    <span class="glyphicon glyphicon-play song-controls action-play-artist" title="<?php echo __('Play all albums'); ?>"></span>
                    <span class="glyphicon glyphicon-random song-controls action-shuffle-artist" title="<?php echo __('Shuffle this artist'); ?>"></span>
                    <span class="dropdown">
                    <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?php echo __('Other actions'); ?>"></span>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-artist-play-next"><?php echo __('Play Next'); ?></a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-artist-to-up-next"><?php echo __('Add to Up Next'); ?></a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-type="artist" data-toggle="modal" data-target="#add-to" ><?php echo __('Add to...'); ?></a></li>
                        </ul>
                    </span>
                </small>
            </h3>
        </div>
        <div class="clearfix"></div>
		<hr />

		<?php foreach ($band['Album'] as $album): ?>
			<!-- Albums view -->
			<div class="col-xs-12" style="margin-bottom: 20px;">
				<div class="col-md-3 hidden-sm hidden-xs text-right">
                    <?php echo $this->Image->lazyload($this->Image->resizedPath($album['cover'], 240, 240), array('alt' => 'Album cover', 'class' => 'img-responsive cover lzld')); ?>
                    <?php if (!empty($album['genres'])): ?>
                        <?php foreach ($album['genres'] as $genre): ?>
                            <?php echo '<span class="label label-info">' . h($genre) . '</span>&nbsp;'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
				</div>

				<div class="col-md-9 col-xs-12 album" data-album="<?php echo h($album['name']); ?>">
					<div class="col-xs-10">
						<h4 class="truncated-name">
                            <?php echo h($album['name']); ?>
                        </h4>
					</div>
					<div class="col-xs-2">
						<h4 class="text-right">
                            <small class="album-year">
                                <?php echo h($album['year']); ?>
                            </small>
                            <small>
                                <span class="glyphicon glyphicon-play song-controls action-play-album" title="<?php echo __('Play all tracks'); ?>"></span>
                                <span class="glyphicon glyphicon-random song-controls action-shuffle-album" title="<?php echo __('Shuffle this album'); ?>"></span>
                                <span class="dropdown">
                                    <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?php echo __('Other actions'); ?>"></span>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-album-play-next"><?php echo __('Play Next'); ?></a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-album-to-up-next"><?php echo __('Add to Up Next'); ?></a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-type="album" data-toggle="modal" data-target="#add-to" ><?php echo __('Add to...'); ?></a></li>
                                    </ul>
                                </span>
                            </small>
                        </h4>
					</div>
					<hr style="clear:both;" />

                    <?php foreach ($album['discs'] as $d => $disc): ?>
                        <!-- Songs list -->
                        <div class="col-lg-6 col-xs-12 album-table-container">
                            <?php if (count($album['discs']) > 1): ?>
                                <p class="disc-number"><strong><?php echo __('Disc') . ' ' . h($d); ?></strong></p>
                            <?php endif; ?>
                            <table class="table table-hover table-album table-album-left">
                                <tbody>
                                <?php $countDiscTracks = count($disc['Track']); ?>
                                <?php $switch = 0; ?>
                                <?php $switchOn = $countDiscTracks > 5 ? round($countDiscTracks / 2) : $countDiscTracks; ?>
                                <?php foreach ($disc['Track'] as $t => $track): ?>

                                    <?php if ($switch == $switchOn): ?>

                                        <!-- Change table in the middle of the album -->
                                        </tbody>
                                        </table>
                                        </div>
                                        <div class="col-lg-6 col-xs-12 album-table-container">
                                        <?php echo count($album['discs']) > 1 ?  '<p class="disc-number visible-lg">&nbsp;</p>' : ''; ?>
                                        <table class="table table-condensed table-hover table-album">
                                        <tbody>

                                    <?php endif; ?>

                                    <!-- Add 'class="on-air"' on play to highlight the row -->
                                    <tr data-id="<?php echo h($track['id']); ?>">
                                        <td class="track-number">
                                            <span class="song-number"><?php echo h($track['track_number']); ?></span>
                                        </td>
                                        <td class="truncated-name">
                                            <?php echo h($track['title']); ?>
                                            <?php if ($track['artist'] != $band['Band']['name']): ?>
                                                <br /><small class="artist-name truncated-name"><?php echo h($track['artist']); ?></small>
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
                        </div><!-- End of songs list -->
                        <div class="clearfix"></div>
                    <?php endforeach; ?>
                </div>
            </div><!-- End of album view -->
        <?php endforeach; ?>
    </div><!-- End of artist view -->
<?php endforeach ?>
