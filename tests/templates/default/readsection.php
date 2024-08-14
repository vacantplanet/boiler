<div><?= $this->body(); ?><?= $text; ?></div>
<?php if ($this->has('list')) { ?>
    <?= $this->section('list'); ?>
<?php } else { ?>
    <p>no list</p>
<?php } ?>
