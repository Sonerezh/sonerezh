<div id="album-expended" data-band="<?php echo h($band);?>" data-album="<?php echo h($album);?>">
    <div class="container">
        <div class="row album" style="margin-bottom: 20px;">
            <div class="col-xs-12">
                <h3><?php echo h($album);?>
                    <small>
                        <span class="glyphicon glyphicon-play song-controls action-play-album" title="<?= ('Play album'); ?>"></span>
                        <span class="glyphicon glyphicon-random song-controls action-shuffle-album" title="<?= ('Shuffle this album'); ?>"></span>
                        <span class="dropdown">
                            <span class="glyphicon glyphicon-plus song-controls" data-toggle="dropdown" title="<?= ('Add to playlist'); ?>"></span>
                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownAlbum">
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-album-play-next"><?= __('Play Next'); ?></a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" class="action-add-album-to-up-next"><?= __('Add to Up Next'); ?></a></li>
                                <li role="presentation"><a role="menuitem" tabindex="-1" href="#" data-toggle="modal" data-type="album" data-target="#add-to" ><?= __('Add to...'); ?></a></li>
                            </ul>
                        </span>
                    </small>
                    <span class="pull-right close-album">&times;</span>
                </h3>
                <?php if(isset($songs[1][0]['Song']['year'])){
                    echo '<h4>'.h($band).'&nbsp;&bull;&nbsp;'.h($songs[1][0]['Song']['year']).'</h4>';
                }else{
                    echo '<h4>'.h($band).'</h4>';
                } ?>
            </div>
            <div class="clearfix"></div>
            <?php foreach($songs as &$album){?>
                <?php if(count($songs) > 1){?>
                <div class="col-xs-12">
                    <p class="disc-number"><strong><?php echo __('Disc').' '.preg_replace('/\/+(\d)$/', '', h($album[0]['Song']['disc'])); ?></strong></p>
                </div>
                <?php }?>
                <div class="col-lg-6 col-xs-12 album-table-container">
                    <table class="table table-condensed table-album table-album-left">
                        <tbody>
                        <?php
                        $switchAs = count($album) > 5 ? round(count($album)/2) : count($album);
                        foreach($album as $i => &$song){?>
                        <?php if($i == $switchAs){ ?>
                        </tbody>
                    </table>
                </div>
                <div class="col-lg-6 col-xs-12 album-table-container">
                    <table class="table table-condensed table-album">
                        <tbody>
                        <?php } ?>
                        <!-- Add 'class="on-air"' on play to highlight the row -->
                        <tr data-id="<?php echo h($song['Song']['id']); ?>">
                            <td class="track-number"><?php echo h($song['Song']['track_number']); ?></td>
                            <td class="truncated-name title">
                                <?php echo h($song['Song']['title']); ?>
                                <?php if($song['Song']['artist'] != $song['Song']['band']){ ?>
                                <br /><small><?php echo h($song['Song']['artist']); ?></small>
                                <?php } ?>
                            </td>
                            <td class="text-right playtime-cell">
                                <span class="song-playtime"><?php echo h($song['Song']['playtime']); ?></span>
                                <?php echo $this->element('add_menu'); ?>
                            </td>
                        </tr>
                        <?php }?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>