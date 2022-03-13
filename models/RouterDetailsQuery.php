<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[RouterDetails]].
 *
 * @see RouterDetails
 */
class RouterDetailsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return RouterDetails[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return RouterDetails|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
