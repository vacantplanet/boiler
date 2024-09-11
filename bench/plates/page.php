<?php $this->layout('layout', ['title' => $title]) ?>

<h1><?= $this->e($title) ?></h1>

<ul>
    <?php foreach ($array as $item) : ?>
        <li><?= $this->e($item) ?></li>
    <?php endforeach ?>
</ul>

<?= $htmlval ?>

<?php $this->insert('insert', ['title' => $title]) ?>

<?php $this->start('script'); ?>
<script>
    console.log('templates');
</script>
<?php $this->stop(); ?>
