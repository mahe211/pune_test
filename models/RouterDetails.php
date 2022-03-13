<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%router_details}}".
 *
 * @property int $id
 * @property string $sap_id
 * @property string $hostname
 * @property string $loopback
 * @property string $mac_address
 * @property int $status
 * @property int $created_at
 * @property int|null $created_by
 * @property int $updated_by
 * @property int|null $updated_at
 * @property string $filename
 */
class RouterDetails extends \yii\db\ActiveRecord {

    public $filename;

    const DUPLICATE_RECORD_HIGHLIGHT = 'table-secondary';
    const INVALID_RECORD_HIGHLIGHT = 'table-danger';

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%router_details}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
//            [['sap_id', 'hostname', 'loopback', 'mac_address'], 'required'],
            [['filename'], 'required', 'on' => 'create'],
            [['filename'], 'file', 'skipOnEmpty' => false, 'extensions' => 'csv', 'on' => 'create'],
            [['filename'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls,xlsx, csv', 'on' => 'import-excel'],
            [['status', 'created_at', 'created_by', 'updated_by', 'updated_at'], 'integer'],
            [['sap_id'], 'string', 'max' => 18],
            [['hostname'], 'string', 'max' => 14],
            [['loopback'], 'string', 'max' => 15],
            [['mac_address'], 'string', 'max' => 17],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'sap_id' => Yii::t('app', 'SAP ID'),
            'hostname' => Yii::t('app', 'Hostname'),
            'loopback' => Yii::t('app', 'Loopback (IPV4)'),
            'mac_address' => Yii::t('app', 'Mac Address'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'filename' => Yii::t('app', 'Import Router Detail'),
            'action' => Yii::t('app', 'Action'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return RouterDetailsQuery the active query used by this AR class.
     */
    public static function find() {
        return new RouterDetailsQuery(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * Validate record already Exists in db 
     * @param string $sapId SAP ID
     * @param string $hostname hostname
     * @return RouterDetails 
     */
    public function validateAlreadyExistsRecord($sapId, $hostname) {
        $routerDetails = RouterDetails::findOne(['sap_id' => $sapId, 'hostname' => $hostname]);
        return $routerDetails;
    }

    /**
     * Validate duplicate hostname exists in db      
     * @param string $hostname hostname
     * @return RouterDetails 
     */
    public function validateHostnameExistsRecord($hostname) {
        $routerDetails = RouterDetails::findOne(['hostname' => $hostname]);
        return $routerDetails;
    }

    /**
     * Validate SAP Id 
     * @param string $sapid SAP ID
     * @return boolean true|false
     */
    public function validateSAPId($sapid) {
        return (strlen($sapid) == 18);
    }

    /**
     * Validate Hostname
     * @param string $hostname
     * @return boolean true|false
     */
    public function validateHostname($hostname) {
        return (strlen($hostname) == 14);
    }

    /**
     * Validate loopback IPV4
     * @param string $loopbackIp IPv4
     * @return boolean true|false
     */
    public function validateLoopback($loopbackIp) {
        return filter_var($loopbackIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * Validate Mac Address
     * @param string $macAddr
     * @return boolean true|false
     */
    public function validateMacAddr($macAddr) {
        return filter_var($macAddr, FILTER_VALIDATE_MAC);
    }

    public function validateDataAndReformat($importDataArr) {
        $dataProvider = array();
        foreach ($importDataArr as $key => $importData) {
            $tempDataProvider = array();
            $sapId = $importData[0];
            $hostname = $importData[1];
            $loopback = $importData[2];
            $macAddress = $importData[3];

            //duplicate record validation
            $tempDataProvider['duplicate'] = null;
            if ($this->validateAlreadyExistsRecord($sapId, $hostname)) {
                $tempDataProvider['duplicate'] = self::DUPLICATE_RECORD_HIGHLIGHT;
            }
            if ($this->validateHostnameExistsRecord($hostname)) {
                $tempDataProvider['duplicate'] = self::DUPLICATE_RECORD_HIGHLIGHT;
            }
            $duplicateArrayExists = array_filter($dataProvider, function($val) use($sapId, $hostname) {
                return ($val['sap_id'] == $sapId and $val['hostname'] == $hostname);
            });
            if (count($duplicateArrayExists) > 0) {
                $tempDataProvider['duplicate'] = self::DUPLICATE_RECORD_HIGHLIGHT;
            }
            $duplicateHostnameArr = array_filter($dataProvider, function($val) use($hostname) {
                return ($val['hostname'] == $hostname);
            });
            if (count($duplicateHostnameArr) > 0) {
                $tempDataProvider['duplicate'] = self::DUPLICATE_RECORD_HIGHLIGHT;
            }

            //Sap Id Validation 
            $tempDataProvider['sap_id'] = $sapId;
            $tempDataProvider['sap_id_valid'] = true;
            $tempDataProvider['sap_id_error_class'] =null;
            if (!$this->validateSAPId($sapId)) {
                $tempDataProvider['sap_id_valid'] = false;
                $tempDataProvider['sap_id_error_class'] = self::INVALID_RECORD_HIGHLIGHT;
            }

            //hostname validation
            $tempDataProvider['hostname'] = $hostname;
            $tempDataProvider['hostname_valid'] = true;
            $tempDataProvider['hostname_error_class'] =null;
            if (!$this->validateHostname($hostname)) {
                $tempDataProvider['hostname_valid'] = false;
                $tempDataProvider['hostname_error_class'] = self::INVALID_RECORD_HIGHLIGHT;
            }

            //Loopback validation
            $tempDataProvider['loopback'] = $loopback;
            $tempDataProvider['loopback_valid'] = true;
            $tempDataProvider['loopback_error_class'] = null;
            if (!$this->validateLoopback($loopback)) {
                $tempDataProvider['loopback_valid'] = false;
                $tempDataProvider['loopback_error_class'] = self::INVALID_RECORD_HIGHLIGHT;
            }

            //mac address validation
            $tempDataProvider['mac_address'] = $macAddress;
            $tempDataProvider['mac_address_valid'] = true;
            $tempDataProvider['mac_address_error_class'] = null;
            if (!$this->validateMacAddr($macAddress)) {
                $tempDataProvider['mac_address_valid'] = false;
                $tempDataProvider['mac_address_error_class'] = self::INVALID_RECORD_HIGHLIGHT;
            }

            $dataProvider[] = $tempDataProvider;
        }

        return $dataProvider;
    }

}
