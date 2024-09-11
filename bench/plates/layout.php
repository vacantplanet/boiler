<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= $this->e($title) ?></title>
    <link rel="stylesheet" href="css/style.css">
    <?php if ($this->section('script')) : ?>
        <?php echo $this->section('script'); ?>
    <?php endif ?>
</head>

<body id="home">
    <?= $this->section('content') ?>
</body>

</html>
