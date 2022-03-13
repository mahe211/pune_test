<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ActiveForm;
use app\models\RouterDetails;

/* @var $this yii\web\View */
/* @var $searchModel app\models\RouterDetailsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $model RouterDetails */

$this->title = Yii::t('app', 'Verify Router Details');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Router Details'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->registerJsFile(
        '@web/js/verify_confirm.js',
        ['depends' => [\yii\web\JqueryAsset::className()]]
);
?>
<div class="router-details-index">

    <h3><?= Html::encode($this->title) ?></h3>
    <?php
    $form = ActiveForm::begin(
                    [
                        'action' => 'validate-save',
                        'options' => [
                            'name' => 'validate_save_csv',
                            'id' => 'validate_save_csv'
                        ],
    ]);
    ?>

    <div class="table-responsive">
        <table class="table table-bordered" id="validation-router-table">
            <thead>
                <tr>
                    <th scope="col"><?= $model->getAttributeLabel('sap_id') ?></th>
                    <th scope="col"><?= $model->getAttributeLabel('hostname') ?></th>
                    <th scope="col"><?= $model->getAttributeLabel('loopback') ?></th>
                    <th scope="col"><?= $model->getAttributeLabel('mac_address') ?></th>
                    <th scope="col"><?= $model->getAttributeLabel('action') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($dataProvider as $key => $data) {
                    $cssRowHighlightClass = $cssSAPHighlightClass = $cssHostnameHighlightClass = $cssLoopbackHighlightClass = $cssMACHighlightClass = null;
                    if ($data['duplicate']) {
                        $cssRowHighlightClass = 'class="' . $model::DUPLICATE_RECORD_HIGHLIGHT . '"';
                    }
                    if (!$data['sap_id_valid']) {
                        $cssSAPHighlightClass = 'class="' . $model::INVALID_RECORD_HIGHLIGHT . '"';
                    }
                    if (!$data['hostname_valid']) {
                        $cssHostnameHighlightClass = 'class="' . $model::INVALID_RECORD_HIGHLIGHT . '"';
                    }
                    if (!$data['loopback_valid']) {
                        $cssLoopbackHighlightClass = 'class="' . $model::INVALID_RECORD_HIGHLIGHT . '"';
                    }
                    if (!$data['mac_address_valid']) {
                        $cssMACHighlightClass = 'class="' . $model::INVALID_RECORD_HIGHLIGHT . '"';
                    }
                    ?>
                    <tr <?= $cssRowHighlightClass ?> id="highlight_duplicate_<?= $key ?>">
                        <td id="SAPHighlight_<?= $key ?>" <?= $cssSAPHighlightClass ?>>

                            <input type="text" class="form-control" id="sap_id_<?= $key ?>" name="sap_id[]" value="<?= $data['sap_id'] ?>">
                        </td>
                        <td id="HostnameHighlight_<?= $key ?>" <?= $cssHostnameHighlightClass ?>>
                            <input type="text" class="form-control" id="hostname_<?= $key ?>" name="hostname[]" value="<?= $data['hostname'] ?>">
                        </td>
                        <td id="LoopbackHighlight_<?= $key ?>" <?= $cssLoopbackHighlightClass ?>>
                            <input type="text" class="form-control" id="loopback_<?= $key ?>" name="loopback[]" value="<?= $data['loopback'] ?>">
                        </td>
                        <td id="MACHighlight_<?= $key ?>" <?= $cssMACHighlightClass ?>>
                            <input type="text" class="form-control" id="mac_address_<?= $key ?>" name="mac_address[]" value="<?= $data['mac_address'] ?>">
                        </td>
                        <td class="text-center">
                            <a class="delete" title="Delete"><i class="fad fa-trash-alt"></i></a>
                            <?php
                            $hiddenInput = Html::input('hidden', 'error_input[]', 0, ['id' => 'error_input_' . $key]);
                            if ($cssRowHighlightClass || $cssSAPHighlightClass || $cssHostnameHighlightClass || $cssLoopbackHighlightClass || $cssMACHighlightClass) {
                                $hiddenInput = Html::input('hidden', 'error_input[]', 1, ['id' => 'error_input_' . $key]);
                            }
                            echo $hiddenInput;
                            ?>
                        </td>
                    </tr>
                <?php }
                ?>
            </tbody>
        </table>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Confirm and Continue'), ['class' => 'btn btn-success', 'name' => 'confirm_continue', 'id' => 'confirm_continue', 'onclick' => "return confirm('We only proceed valid records, invalid records will not save, are you sure to continue?');"]) ?>
        <?= Html::button(Yii::t('app', 'Validate'), ['class' => 'btn btn-success', 'name' => 'validatecsv', 'id' => 'validatecsv']) ?>
    </div>
    <?php ActiveForm::end(); ?>
    <div>
        <table class="table table-hover table-borderless">
            <thead>
                <tr><td scope="col" class="text-info">*Note:</td></tr>
                <tr><td scope="col" class="text-secondary">Duplicate entries, rows highlighted in gray color </td></tr>
                <tr><td scope="col" class="text-danger">Invalid cells, highlighted in red color.</td></tr>
                <tr><td scope="col" >Cell contains no fill means record is valid.</td></tr>
            </thead>
        </table>
    </div>
</div>
