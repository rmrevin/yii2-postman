<?php
/**
 * LetterQuery.php
 * @author Revin Roman http://phptime.ru
 */

namespace rmrevin\yii\postman\models\queries;

/**
 * Class LetterQuery
 * @package rmrevin\yii\postman\models\queries
 */
class LetterQuery extends \yii\db\ActiveQuery
{

    /**
     * @param integer|array $id
     * @return static
     */
    public function byId($id)
    {
        $this->andWhere(['id' => $id]);

        return $this;
    }

    /**
     * @param string|array $code
     * @return static
     */
    public function byCode($code)
    {
        $this->andWhere(['code' => $code]);

        return $this;
    }

    /**
     * @return static
     */
    public function onlyNotSend()
    {
        $this->andWhere('[[date_send]] = :date OR [[date_send]] IS NULL', [':date' => '0000-00-00 00:00:00']);

        return $this;
    }
}