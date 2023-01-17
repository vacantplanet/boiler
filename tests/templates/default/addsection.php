<?php $this->layout('readsection'); ?>
<p><?php echo $text; ?></p>
<?php $this->begin('list'); ?>
<ul>
    <li><?php echo $text; ?></li>
</ul>
<?php $this->end(); ?>
