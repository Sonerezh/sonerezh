<ul class="pagination">
    <li <?php if(!$this->Paginator->hasPrev()){echo 'class="disabled"';}?>><?= $this->Paginator->prev('«',array('tag' => false));?></li>
    <?= $this->Paginator->numbers(array(
        'tag' => 'li',
        'separator' => false,
        'currentTag' => 'a',
        'currentClass' => 'active'
    ));?>
    <li <?php if(!$this->Paginator->hasNext()){echo 'class="disabled"';}?>><?= $this->Paginator->next('»',array('tag' => false));?></li>
</ul>