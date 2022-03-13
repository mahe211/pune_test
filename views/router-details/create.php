<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\RouterDetails */

$this->title = ($importFileType == 'excel') ? Yii::t('app', 'Upload Router Details Excel') : Yii::t('app', 'Upload Router Details CSV');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Router Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="router-details-create">    
    <h4><?= Html::encode($this->title) ?></h4>
    <?php
    if ($importFileType == 'excel') {
        echo $this->render('_import_excel', [
            'model' => $model,
        ]);
    } else {
        echo $this->render('_import_csv', [
            'model' => $model,
        ]);
    }
    ?>
</div>
