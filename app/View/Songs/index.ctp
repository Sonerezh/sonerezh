<?php if(!empty($songs)){ ?>
    <div class="col-lg-12">
        <table class="table table-striped table-condensed table-hover" data-scroll-container="true">
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
            <?php foreach($songs as $song){ ?>
                <!-- Add 'class="on-air"' on play to highlight the row -->
                <tr data-id="<?php echo h($song['Song']['id']);?>" data-scroll-content="true">
                    <td class="track-number">
                        <span class="song-number"><?php echo h($song['Song']['track_number']); ?></span>
                    </td>
                    <td class="truncated-name"><?php echo h($song['Song']['title']); ?></td>
                    <td class="truncated-name hidden-xs hidden-sm"><?php echo h($song['Song']['band']); ?></td>
                    <td class="truncated-name visible-lg"><?php echo h($song['Song']['album']); ?></td>
                    <td class="text-right playtime-cell">
                        <span class="song-playtime"><?php echo h($song['Song']['playtime']); ?></span>
                        <?php echo $this->element('add_menu'); ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php echo $this->element('add_to_playlist'); ?>
    <?php echo $this->element('pagination');?>
<?php }?>
