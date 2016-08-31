<?php
/* @var $caption string */
/* @var $values array */

use yii\helpers\Html;
?>
<?php echo $caption ? "<h3>{$caption}</h3>" : ''; ?>

<?php if (empty($values)): ?>

    <p>Empty.</p>

<?php else:	?>

    <table class="table table-condensed table-bordered table-striped table-hover request-table" style="table-layout: fixed;">
        <thead>
            <tr>
                <th style="width: 160px;">名称</th>
                <th style="width: 160px;">类型</th>
                <th>注释</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($values as $value): ?>
            <tr>
                <td><?= Html::encode($value['name']) ?></td>
                <td><?= Html::encode($value['type']) ?></td>
                <td><?= Html::encode($value['desc']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>
