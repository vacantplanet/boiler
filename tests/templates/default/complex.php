<?php require 'header.php'; ?>

<body>
    <h1><?php echo $headline; ?></h1>
    <table>
        <?php foreach ($array as $key => $value) { ?>
            <tr>
                <td><?php echo $this->e($key); ?></td>
                <?php foreach ($value as $item) { ?>
                    <td><?php echo $item; ?></td>
                <?php } ?>
            </tr>
        <?php } ?>
    </table><?php echo $html->unwrap(); ?>
</body>

</html>
