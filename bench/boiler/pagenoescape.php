<?php $this->layout('layout') ?>

<h1><?= $title ?></h1>

<ul>
    <?php foreach ($array as $item) : ?>
        <li><?= $this->e($item) ?></li>
    <?php endforeach ?>
</ul>

<?= $htmlval ?>

<?php $this->insert('insert') ?>

<?php $this->begin('script'); ?>
<script>
    console.log('templates');
</script>
<?php $this->end(); ?>
