<?php $this->start('script');
echo $this->Html->script('albums');
$this->end(); ?>

<div class="col-lg-12" data-scroll-container="true" data-view="albums">
    <?php if (!empty($latests) && $this->Paginator->current('Song') == 1): ?>
        <h3><?php echo __('Recently added'); ?></h3>
        <?php $i = 1; ?>
        <?php $hidden = ''; ?>
        <?php foreach($latests as $album): ?>
            <?php if ($i == 4) {
                $hidden = 'hidden-xs';
            } else if ($i >= 5) {
                    $hidden = 'hidden-sm hidden-xs';
            } ?>
            <div class="col-md-2 col-sm-3 col-xs-4 action-expend <?php echo $hidden; ?>" data-band="<?php echo h($album['Song']['band']); ?>" data-album="<?php echo h($album['Song']['album']); ?>" data-scroll-content="true">
                <?php echo $this->Image->lazyload($this->Image->resizedPath($album['Song']['cover'], 220, 220), array('class' => 'img-responsive cover lzld', 'style' => 'cursor: pointer;'));?>
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
            } else if ($i % 4 == 0) {
                $clear = "visible-sm";
            } else if ($i % 3 == 0) {
                $clear = "visible-xs";
            }
            ?>
            <?php if ($clear !== false): ?>
                <div class="clearfix <?php echo $clear;?>" data-scroll-content="true"></div>
            <?php endif;
            $i++; ?>
        <?php endforeach; ?>
        <hr />
    <?php endif; ?>
    <h3><?php echo __('All albums'); ?></h3>
    <?php $j = 1; ?>
	<?php foreach($songs as $album): ?>
        <div class="col-md-2 col-sm-3 col-xs-4 action-expend" data-band="<?php echo h($album['Song']['band']); ?>" data-album="<?php echo h($album['Song']['album']); ?>" data-scroll-content="true">
            <?php echo $this->Image->lazyload($this->Image->resizedPath($album['Song']['cover'], 220, 220), array('class' => 'img-responsive cover lzld', 'style' => 'cursor: pointer;'));?>
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
            } else if ($j % 4 == 0) {
                $clear = "visible-sm";
            } else if ($j % 3 == 0) {
                $clear = "visible-xs";
            }
        ?>
        <?php if ($clear !== false): ?>
            <div class="clearfix <?php echo $clear;?>" data-scroll-content="true"></div>
        <?php endif;
        $j++; ?>
	<?php endforeach; ?>
</div>
<?php echo $this->element('add_to_playlist'); ?>
<?php echo $this->element('pagination');?>