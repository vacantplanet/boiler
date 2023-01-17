<div><?php echo $this->body(); ?><?php echo $text; ?></div>
<?php if ($this->has('list')) { ?>
    <?php echo $this->section('list'); ?>
<?php } else { ?>
    <p>no list</p>
<?php } ?>
