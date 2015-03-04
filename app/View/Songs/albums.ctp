<?php $this->start('script');
echo $this->Html->script('albums');
$this->end(); ?>

<div class="col-lg-12" data-scroll-container="true">
	<?php
    $i=1;
    foreach($songs as $album){ ?>
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
        if($i%6==0){
            $clear = "visible-lg visible-md visible-xs";
            if($i%4==0){
                $clear .= " visible-sm";
            }
        }else if($i%4==0){
            $clear = "visible-sm";
        }else if($i%3==0){
            $clear = "visible-xs";
        }
        if($clear !== false){?>
            <div class="clearfix <?php echo $clear;?>" data-scroll-content="true"></div>
        <?php }
        $i++;
	} ?>
</div>
<?php echo $this->element('add_to_playlist'); ?>
<?php echo $this->element('pagination');?>