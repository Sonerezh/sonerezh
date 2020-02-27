<?php $this->start('script');
echo $this->Html->script('albums');
$this->end(); ?>

<div class="col-lg-12" data-scroll-container="true" data-view="albums">
    <?php
    $setting = ClassRegistry::init('Setting');
    $settings = $setting->find('first', array('fields' => array('Setting.enable_random')));
    $random_enabled = $settings['Setting']['enable_random'];
    $settings = $setting->find('first', array('fields' => array('Setting.enable_recent')));
    $recent_enabled = $settings['Setting']['enable_recent'];
    ?>

    <?php if ($recent_enabled): ?>
        <?php if (!empty($latests) && $this->Paginator->current('Song') == 1): ?>
            <h3><?php echo __('Recently added'); ?></h3>
            <?php $i = 1; ?>
            <?php $hidden = ''; ?>
            <?php foreach ($latests as $album): ?>
                <?php if ($i == 4) {
                    $hidden = 'hidden-xs';
                } elseif ($i >= 5) {
                        $hidden = 'hidden-sm hidden-xs';
                } ?>
                <div class="col-md-2 col-sm-3 col-xs-4 action-expend <?php echo $hidden; ?>" data-band="<?php echo h($album['Song']['band']); ?>" data-album="<?php echo h($album['Song']['album']); ?>" data-scroll-content="true">
                    <?php echo $this->Image->lazyload($this->Image->resizedPath($album['Song']['cover'], 220, 220), array('class' => 'img-responsive cover lzld', 'style' => 'cursor: pointer;')); ?>
                    <h4 class="truncated-name" title="<?php echo h($album['Song']['album']); ?>">
                        <?php echo h($album['Song']['album']); ?>
                        <small>
                            <br /><?php echo h($album['Song']['band']); ?>
                        </small>
                    </h4>
                </div>
                <?php
                $clear = false;
                if ($i % 6 == 0) {
                    $clear = "visible-lg visible-md visible-xs";
                    if ( $i % 4 == 0) {
                        $clear .= " visible-sm";
                    }
                } elseif ($i % 4 == 0) {
                    $clear = "visible-sm";
                } elseif ($i % 3 == 0) {
                    $clear = "visible-xs";
                 }
                ?>
                <?php if ($clear !== false): ?>
                    <div class="clearfix <?php echo $clear; ?>" data-scroll-content="true"></div>
                <?php endif;
                $i++; ?>
            <?php endforeach; ?>
            <div class="clearfix"></div>
            <hr />
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($random_enabled): ?>
        <?php if (!empty($rands) && $this->Paginator->current('Song') == 1): ?>
            <h3><?php echo __('Random albums'); ?></h3>
            <?php $i = 1; ?>
            <?php $hidden = ''; ?>
            <?php foreach ($rands as $album): ?>
                <?php if ($i == 4) {
                    $hidden = 'hidden-xs';
                } elseif ($i >= 5) {
                        $hidden = 'hidden-sm hidden-xs';
                } ?>
                <div class="col-md-2 col-sm-3 col-xs-4 action-expend <?php echo $hidden; ?>" data-band="<?php echo h($album['Song']['band']); ?>" data-album="<?php echo h($album['Song']['album']); ?>" data-scroll-content="true">
                    <?php echo $this->Image->lazyload($this->Image->resizedPath($album['Song']['cover'], 220, 220), array('class' => 'img-responsive cover lzld', 'style' => 'cursor: pointer;')); ?>
                    <h4 class="truncated-name" title="<?php echo h($album['Song']['album']); ?>">
                        <?php echo h($album['Song']['album']); ?>
                        <small>
                            <br /><?php echo h($album['Song']['band']); ?>
                        </small>
                    </h4>
                </div>
                <?php
                $clear = false;
                if ($i % 6 == 0) {
                    $clear = "visible-lg visible-md visible-xs";
                    if ( $i % 4 == 0) {
                        $clear .= " visible-sm";
                    }
                } elseif ($i % 4 == 0) {
                    $clear = "visible-sm";
                } elseif ($i % 3 == 0) {
                    $clear = "visible-xs";
                }
                ?>
                <?php if ($clear !== false): ?>
                    <div class="clearfix <?php echo $clear; ?>" data-scroll-content="true"></div>
                <?php endif;
                $i++; ?>
            <?php endforeach; ?>
            <div class="clearfix"></div>
            <hr />
        <?php endif; ?>
     <?php endif; ?>

    <?php if (!empty($songs)): ?>
        <h3>
            <?php echo __('All albums'); ?>
            <small>
                <?php echo $this->Html->link(
                    __('Sort by album'),
                    array('controller' => 'songs', 'action' => 'albums', '?' => array('sort' => 'album')),
                    array('class' => 'label label-info pull-right', 'style' => 'margin-left: 2px;')
                ); ?>
                <?php echo $this->Html->link(
                    __('Sort by band'),
                    array('controller' => 'songs', 'action' => 'albums', '?' => array('sort' => 'band')),
                    array('class' => 'label label-info pull-right', 'style' => 'margin-right: 2px;')
                ); ?>
            </small>
        </h3>
    <?php endif; ?>

    <?php $j = 1; ?>
	<?php foreach ($songs as $album): ?>
        <div class="col-md-2 col-sm-3 col-xs-4 action-expend" data-band="<?php echo h($album['Song']['band']); ?>" data-album="<?php echo h($album['Song']['album']); ?>" data-scroll-content="true">
            <?php echo $this->Image->lazyload($this->Image->resizedPath($album['Song']['cover'], 220, 220), array('class' => 'img-responsive cover lzld', 'style' => 'cursor: pointer;')); ?>
            <h4 class="truncated-name" title="<?php echo h($album['Song']['album']); ?>">
                <?php echo h($album['Song']['album']); ?>
                <small>
                    <br /><?php echo h($album['Song']['band']); ?>
                </small>
            </h4>
		</div>
        <?php
            $clear = false;
            if ($j % 6 == 0) {
                $clear = "visible-lg visible-md visible-xs";
                if ( $j % 4 == 0) {
                    $clear .= " visible-sm";
                }
            } elseif ($j % 4 == 0) {
                $clear = "visible-sm";
            } elseif ($j % 3 == 0) {
                $clear = "visible-xs";
            }
        ?>
        <?php if ($clear !== false): ?>
            <div class="clearfix <?php echo $clear; ?>" data-scroll-content="true"></div>
        <?php endif;
        $j++; ?>
	<?php endforeach; ?>
</div>
<?php echo $this->element('add_to_playlist'); ?>
<?php echo $this->element('pagination'); ?>
