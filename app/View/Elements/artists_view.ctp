<?php foreach($songs as $band => $songs): ?>
	<!-- Artists view -->
	<div class="col-xs-12 band-name" data-band="<?php echo h($band);?>" data-scroll-content="true">
        <div class="col-xs-10">
		<h3>
            <?php echo h($band); ?>
        </h3>
		<p class="band-stats">
            <small>
                <?php
                    echo __n("%s Album", "%s Albums", count($songs['albums']), count($songs['albums'])).', ';
                    echo __n("%s Song", "%s Songs", $songs['sCount'], $songs['sCount']);
                ?>
            </small>
        </p>
        </div>
        <div class="col-xs-2">
            <h3 class="text-right">
                <small>
                    <span class="glyphicon glyphicon-play song-controls action-play-artist" title="<?= ('Play all albums'); ?>"></span>
                    <span class="glyphicon glyphicon-random song-controls action-shuffle-artist" title="<?= ('Shuffle this artist'); ?>"></span>
                    <span class="dropdown">
                    <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?= ('Add to playlist'); ?>"></span>
                        <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-artist-play-next"><?= __('Play Next'); ?></a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-artist-to-up-next"><?= __('Add to Up Next'); ?></a></li>
                            <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-type="artist" data-toggle="modal" data-target="#add-to" ><?= __('Add to...'); ?></a></li>
                        </ul>
                    </span>
                </small>
            </h3>
        </div>
        <div class="clearfix"></div>
		<hr />

		<?php foreach($songs['albums'] as $album):  ?>
			<!-- Albums view -->
			<div class="col-xs-12" style="margin-bottom: 20px;">
				<div class="col-md-3 hidden-sm hidden-xs text-right">
                    <?php echo $this->Image->lazyload($this->Image->resizedPath($album['cover'], 240, 240), array('alt' => 'Album cover', 'class' => 'img-responsive cover lzld')); ?>
                    <?php if(!empty($album['genre'])){
                        foreach($album['genre'] as $genre){
                            echo '<span class="label label-info">'.h($genre).'</span>&nbsp;';
                        }
                    } ?>
				</div>

				<div class="col-md-9 col-xs-12 album" data-album="<?php echo h($album['album']);?>">
					<div class="col-xs-10">
						<h4 class="truncated-name">
                            <?php echo h($album['album']); ?>
                        </h4>
					</div>
					<div class="col-xs-2">
						<h4 class="text-right">
                            <small class="album-year">
                                <?php echo h($album['year']); ?>
                            </small>
                            <small>
                                <span class="glyphicon glyphicon-play song-controls action-play-album" title="<?= ('Play all tracks'); ?>"></span>
                                <span class="glyphicon glyphicon-random song-controls action-shuffle-album" title="<?= ('Shuffle this album'); ?>"></span>
                                <span class="dropdown">
                                    <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?= ('Add to playlist'); ?>"></span>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-album-play-next"><?= __('Play Next'); ?></a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-album-to-up-next"><?= __('Add to Up Next'); ?></a></li>
                                        <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-type="album" data-toggle="modal" data-target="#add-to" ><?= __('Add to...'); ?></a></li>
                                    </ul>
                                </span>
                            </small>
                        </h4>
					</div>
					<hr  style="clear:both;" />

                    <?php foreach($album['discs'] as $keyDisc => $disc): ?>

                        <!-- Songs list -->
                        <div class="col-lg-6 col-xs-12 album-table-container">
                            <?php if(count($album['discs']) > 1){ ?>
                                <p class="disc-number"><strong><?= __('Disc') ?> <?php echo h($keyDisc); ?></strong></p>
                            <?php } ?>
                            <table class="table table-striped table-condensed table-hover table-album table-album-left">
                                <tbody>
                                <?php $switchOn = count($disc['songs']) > 5 ? round(count($disc['songs']) / 2) : count($disc['songs']); ?>
                                <?php foreach($disc['songs'] as $key => $song): ?>

                                    <?php if($key == $switchOn): ?>

                                        <!-- Change table in the middle of the album -->
                                        </tbody>
                                        </table>
                                        </div>
                                        <div class="col-lg-6 col-xs-12 album-table-container">
                                        <?= count($album['discs']) > 1 ?  '<p class="disc-number visible-lg">&nbsp;</p>' : ''; ?>
                                        <table class="table table-striped table-condensed table-hover table-album">
                                        <tbody>

                                    <?php endif; ?>

                                    <!-- Add 'class="on-air"' on play to highlight the row -->
                                    <tr data-id="<?php echo h($song['id']);?>">
                                        <td class="track-number">
                                            <span class="song-number"><?php echo h($song['track_number']); ?></span>
                                        </td>
                                        <td class="truncated-name">
                                            <?php echo h($song['title']); ?>
                                            <?php if($song['artist'] != $song['band']){ ?>
                                                <br /><small class="artist-name truncated-name"><?php echo h($song['artist']); ?></small>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right playtime-cell">
                                            <span class="song-playtime"><?php echo h($song['playtime']); ?></span>
                                            <?php echo $this->element('add_menu'); ?>
                                        </td>
                                    </tr>
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
