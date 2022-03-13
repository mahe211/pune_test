<?php

namespace app\controllers;

use app\models\RouterDetails;
use app\models\RouterDetailsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * RouterDetailsController implements the CRUD actions for RouterDetails model.
 */
class RouterDetailsController extends Controller {

    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
                parent::behaviors(),
                [
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Lists all RouterDetails models.
     *
     * @return string
     */
    public function actionIndex() {
        $searchModel = new RouterDetailsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new RouterDetails model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate() {
        $model = new RouterDetails();
        $model->scenario = 'create';
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $csvFile = \yii\web\UploadedFile::getInstance($model, 'filename');

                $handle = fopen($csvFile->tempName, 'r');
                $row = 0;
                $dataProvider = array();
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if ($row > 0) {
                        $csvDataSapId = $data[0];
                        $csvDataHostname = $data[1];
                        $csvDataLoopback = $data[2];
                        $csvDataMacAddr = $data[3];
                        $tempDataProvider = array();

                        $tempDataProvider['duplicate'] = false;

                        if ($model->validateAlreadyExistsRecord($csvDataSapId, $csvDataHostname)) {
                            $tempDataProvider['duplicate'] = true;
                        }
                        if ($model->validateHostnameExistsRecord($csvDataHostname)) {
                            $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                        }
                        $duplicateArrayExists = array_filter($dataProvider, function($val) use($csvDataSapId, $csvDataHostname) {
                            return ($val['sap_id'] == $csvDataSapId and $val['hostname'] == $csvDataHostname);
                        });
                        if (count($duplicateArrayExists) > 0) {
                            $tempDataProvider['duplicate'] = true;
                        }
                        $duplicateHostnameArr = array_filter($dataProvider, function($val) use($csvDataHostname) {
                            return ($val['hostname'] == $csvDataHostname);
                        });
                        if (count($duplicateHostnameArr) > 0) {
                            $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                        }

                        $tempDataProvider['sap_id'] = $csvDataSapId;
                        $tempDataProvider['sap_id_valid'] = true;
                        if (!$model->validateSAPId($csvDataSapId)) {
                            $tempDataProvider['sap_id_valid'] = false;
                        }

                        $tempDataProvider['hostname'] = $csvDataHostname;
                        $tempDataProvider['hostname_valid'] = true;
                        if (!$model->validateHostname($csvDataHostname)) {
                            $tempDataProvider['hostname_valid'] = false;
                        }

                        $tempDataProvider['loopback'] = $csvDataLoopback;
                        $tempDataProvider['loopback_valid'] = true;
                        if (!$model->validateLoopback($csvDataLoopback)) {
                            $tempDataProvider['loopback_valid'] = false;
                        }

                        $tempDataProvider['mac_address'] = $csvDataMacAddr;
                        $tempDataProvider['mac_address_valid'] = true;
                        if (!$model->validateMacAddr($csvDataMacAddr)) {
                            $tempDataProvider['mac_address_valid'] = false;
                        }
                        $dataProvider[] = $tempDataProvider;
                    }
                    $row++;
                }

                return $this->render('verify-confirm', [
                            'model' => $model,
                            'dataProvider' => $dataProvider,
                ]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
                    'importFileType' => 'csv'
        ]);
    }

    /**
     * Updates an existing RouterDetails model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes an existing RouterDetails model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the RouterDetails model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return RouterDetails the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = RouterDetails::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }

    public function actionDownloadSampleCsv() {
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename="router_details_sample.csv"');
        // do not cache the file
        header('Pragma: no-cache');
        header('Expires: 0');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['sap_id', 'hostname', 'loopback', 'mac_address']);
        fputcsv($output, ['SAP-IN-MHMUM-1234A', 'INMHMUM1234AAA', '255.255.255.255', 'D8-9C-67-AE-5B-21']);
        fclose($output);
        exit();
    }

    public function actionDownloadSampleExcel() {
        $objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->setTitle('router_details_sample');

        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 1, 'sap_id');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 1, 'hostname');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 1, 'loopback');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 1, 'mac_address');


        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 2, 'SAP-IN-MHMUM-1234A');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 2, 'INMHMUM1234AAA');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 2, '255.255.255.255');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 2, 'D8-9C-67-AE-5B-21');

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=router_details_sample.xls');
        header('Cache-Control: max-age=0');
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($objPHPExcel, 'Xls');
        $objWriter->save('php://output');
        die();
    }

    /**
     * Validate JS data 
     * @return array of data 
     */
    public function actionValidateJsData() {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            $sapIds = Yii::$app->request->post('sap_id');
            $hostnames = Yii::$app->request->post('hostname');
            $loopbacks = Yii::$app->request->post('loopback');
            $macAddresses = Yii::$app->request->post('mac_address');
            $dataProvider = array();
            $model = new RouterDetails();
            foreach ($sapIds as $key => $sapId) {
                $hostname = $hostnames[$key];
                $loopback = $loopbacks[$key];
                $MacAddr = $macAddresses[$key];
                $tempDataProvider = array();

                $tempDataProvider['duplicate'] = null;
                if ($model->validateAlreadyExistsRecord($sapId, $hostname)) {
                    $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                }
                if ($model->validateHostnameExistsRecord($hostname)) {
                    $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                }
                $duplicateArrayExists = array_filter($dataProvider, function($val) use($sapId, $hostname) {
                    return ($val['sap_id'] == $sapId and $val['hostname'] == $hostname);
                });
                if (count($duplicateArrayExists) > 0) {
                    $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                }
                $duplicateHostnameArr = array_filter($dataProvider, function($val) use($hostname) {
                    return ($val['hostname'] == $hostname);
                });
                if (count($duplicateHostnameArr) > 0) {
                    $tempDataProvider['duplicate'] = $model::DUPLICATE_RECORD_HIGHLIGHT;
                }

                $tempDataProvider['sap_id'] = $sapId;
                $tempDataProvider['sap_id_error_class'] = null;
                if (!$model->validateSAPId($sapId)) {
                    $tempDataProvider['sap_id_error_class'] = $model::INVALID_RECORD_HIGHLIGHT;
                }

                $tempDataProvider['hostname'] = $hostname;
                $tempDataProvider['hostname_error_class'] = null;
                if (!$model->validateHostname($hostname)) {
                    $tempDataProvider['hostname_error_class'] = $model::INVALID_RECORD_HIGHLIGHT;
                }

                $tempDataProvider['loopback'] = $loopback;
                $tempDataProvider['loopback_error_class'] = null;
                if (!$model->validateLoopback($loopback)) {
                    $tempDataProvider['loopback_error_class'] = $model::INVALID_RECORD_HIGHLIGHT;
                }

                $tempDataProvider['mac_address'] = $MacAddr;
                $tempDataProvider['mac_address_error_class'] = null;
                if (!$model->validateMacAddr($MacAddr)) {
                    $tempDataProvider['mac_address_error_class'] = $model::INVALID_RECORD_HIGHLIGHT;
                }
                $dataProvider[] = $tempDataProvider;
            }
        }
        return $dataProvider;
    }

    public function actionValidateSave() {
        $model = new RouterDetails();
        if ($this->request->isPost) {
            $errorInputArr = $this->request->post('error_input');
            $sapIdArr = Yii::$app->request->post('sap_id');
            $hostnameArr = Yii::$app->request->post('hostname');
            $loopbackArr = Yii::$app->request->post('loopback');
            $macAddressArr = Yii::$app->request->post('mac_address');
            $errorRecord = $successRecord = 0;
            foreach ($errorInputArr as $key => $errorInputVal) {
                if ($errorInputVal == 1) {
                    $errorRecord++;
                    continue;
                }
                $model = new RouterDetails();
                $model->sap_id = $sapIdArr[$key];
                $model->hostname = $hostnameArr[$key];
                $model->loopback = $loopbackArr[$key];
                $model->mac_address = $macAddressArr[$key];
                $model->save();
                $successRecord++;
            }
            if ($errorRecord > 0 && $successRecord > 0) {
                Yii::$app->session->setFlash('warning', 'Records partially saved! Success count- ' . $successRecord . '  Failure Count – ' . $errorRecord . ' ', false);
            }
            // All records failed
            if ($errorRecord > 0 && $successRecord == 0) {
                Yii::$app->session->setFlash('error', 'Unfortunately!  All records are invalid, please try again to import and save it.', false);
            }
            // All records successed
            if ($errorRecord == 0 && $successRecord > 0) {
                Yii::$app->session->setFlash('success', 'Great! All are valid record and saved.', false);
            }
            return $this->redirect('index');
        }
        return $this->redirect('create');
    }

    public function actionImportExcel() {
        $model = new RouterDetails();
        $model->scenario = 'import-excel';
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $importFile = \yii\web\UploadedFile::getInstance($model, 'filename');

                $handle = fopen($importFile->tempName, 'r');

                $inputFileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($importFile->tempName);
                $objReader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($inputFileType);
                $objPHPExcel = $objReader->load($importFile->tempName);

                $num = $objPHPExcel->getSheetCount();
                $sheetnames = $objPHPExcel->getSheetNames();
                if ($num > 1) {
                    $model->addError('filename', 'Sheets are more than 1, kindly refer and download sample file');
                    return $this->render('create', [
                                'model' => $model,
                                'importFileType' => 'excel'
                    ]);
                }
                $importDataArr = array();
                for ($i = 0; $i < $num; $i++) {
                    $sheet = $objPHPExcel->getSheet($i);
                    $highestRow = $sheet->getHighestRow();
                    $highestColumn = $sheet->getHighestColumn();

                    //$row is start 2 because first row set as header.        
                    for ($row = 2; $row <= $highestRow; ++$row) {
                        $rowData = $sheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                        $emptyRow = true;

                        foreach ($rowData as $k => $v) {
                            if ($v) {
                                $emptyRow = false;
                            }
                        }
                        if ($emptyRow) {
                            continue;
                        }
                        $importDataArr[] = $rowData[0];
                    }
                }
                $dataProvider = $model->validateDataAndReformat($importDataArr);
                return $this->render('verify-confirm', [
                            'model' => $model,
                            'dataProvider' => $dataProvider,
                ]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
                    'importFileType' => 'excel'
        ]);
    }

}
