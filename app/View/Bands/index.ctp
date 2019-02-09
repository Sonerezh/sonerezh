<div data-scroll-container="true" data-view="artists">
    <?php if (empty($bands)): ?>
        <?php echo $this->element('empty_db'); ?>
    <?php else: ?>
        <?php echo $this->element('artists_view'); ?>
        <?php echo $this->element('add_to_playlist'); ?>
        <?php echo $this->element('pagination'); ?>
    <?php endif; ?>
</div>