<?php $this->layout('layout') ?>

<h1><?= $title ?></h1>

<ul>
    <?php foreach ($array as $item) : ?>
        <li><?= $item ?></li>
    <?php endforeach ?>
</ul>

<?= $htmlval->unwrap() ?>

<?php $this->insert('insert') ?>

<?php $this->begin('script'); ?>
<script>
    console.log('templates');
</script>
<?php $this->end(); ?>
