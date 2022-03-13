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
                $validateImportData = array();
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $tempArr = array();
                    if ($row > 0) {
                        $tempArr[] = $data[0];
                        $tempArr[] = $data[1];
                        $tempArr[] = $data[2];
                        $tempArr[] = $data[3];
                        $validateImportData[] = $tempArr;
                    }
                    $row++;
                }

                $dataProvider = $model->validateDataAndReformat($validateImportData);

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

    /**
     * Download CSV file 
     */
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

    /**
     * Download sample Excel file
     */
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
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 2, '255.254.254.255');
        $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 2, 'D8-9C-67-AE-5B-22');

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
            $validateImportData = array();
            foreach ($sapIds as $key => $sapId) {
                $tempArr[0] = $sapId;
                $tempArr[1] = $hostnames[$key];
                $tempArr[2] = $loopbacks[$key];
                $tempArr[3] = $macAddresses[$key];
                $validateImportData[] = $tempArr;
            }
            $model = new RouterDetails();
            $dataProvider = $model->validateDataAndReformat($validateImportData);
            return $dataProvider;
        }
    }

    /**
     * Validate and save, only those record will saved which have no error 
     * @return object
     */
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
                Yii::$app->session->setFlash('warning', 'Records partially saved! Success counts - ' . $successRecord . '  Failure Counts – ' . $errorRecord . ' ', false);
            }
            // All records failed
            if ($errorRecord > 0 && $successRecord == 0) {
                Yii::$app->session->setFlash('error', 'Unfortunately!  All records are invalid, please try again to import and save it.', false);
            }
            // All records successed
            if ($errorRecord == 0 && $successRecord > 0) {
                Yii::$app->session->setFlash('success', 'Great! All are valid records and saved into DB.', false);
            }
            return $this->redirect('index');
        }
        return $this->redirect('create');
    }

    /**
     * Import excel, validate it.
     * @return type
     */
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
