<ul class="pagination">
    <li <?php if (!$this->Paginator->hasPrev()) { echo 'class="disabled"'; } ?>><?php echo $this->Paginator->prev('«',array('tag' => false)); ?></li>
    <?php echo $this->Paginator->numbers(array(
        'tag' => 'li',
        'separator' => false,
        'currentTag' => 'a',
        'currentClass' => 'active'
    )); ?>
    <li <?php if (!$this->Paginator->hasNext()) { echo 'class="disabled"'; } ?>><?php echo $this->Paginator->next('»',array('tag' => false)); ?></li>
</ul>