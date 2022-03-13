<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\RouterDetails;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RouterDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Router Details');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="router-details-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php //  Html::a(Yii::t('app', 'Import CSV File'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import Excel File'), ['import-excel'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Download Sample CSV File'), ['download-sample-csv'], ['class' => 'btn btn-info']) ?>
        <?= Html::a(Yii::t('app', 'Download Sample Excel File'), ['download-sample-excel'], ['class' => 'btn btn-info']) ?>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]);  ?>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'sap_id',
            'hostname',
            'loopback',
            'mac_address',
            'created_at:datetime',            
            'updated_at:datetime',
        ],
    ]);
    ?>

    <?php Pjax::end(); ?>

</div>
