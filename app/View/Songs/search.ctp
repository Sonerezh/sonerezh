<div class="col-xs-12" style="margin-top: 20px;">
    <form class="form-horizontal" action="<?= $this->Html->url(array('controller' => 'songs', 'action' => 'search')); ?>" role="search">
        <div class="form-group">
            <label for="inputSearch" class="col-xs-2 control-label"><?= __('Looking for'); ?></label>
            <div class="col-xs-8">
                <input type="text" class="form-control search-input" placeholder="Nirvana" name="q" value="<?= isset($query) ? h($query) : ''; ?>"/>
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-10">
                <button type="submit" class="btn btn-default btn-primary pull-right"><?= __('Search'); ?></button
            </div>
        </div>
    </form>
</div>

<?php if($query){ ?>
    <div data-scroll-container="true">
        <?php echo $this->element('artists_view'); ?>
        <?php echo $this->element('add_to_playlist'); ?>
        <?php echo $this->element('pagination');?>
    </div>
<?php } ?>
