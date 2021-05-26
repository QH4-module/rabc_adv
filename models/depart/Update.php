<?php
/**
 * File Name: Update.php
 * Automatically generated by QHGC tool
 * @date 2021-03-16 11:25:21
 * @version 4.0.0
 */

namespace qh4module\rabc_adv\models\depart;


use qh4module\rabc_adv\HpRabcAdv;
use QTTX;
use qttx\helper\ArrayHelper;

/**
 * Class Update
 * 更新tbl_bk_depart表单条数据
 * @package backend\models\models\rabc
 */
class Update extends DepartModel
{

    /**
     * @var string 接收参数,必须：id
     */
    public $id;

    /**
     * @var string 接收参数,必须：部门名称
     */
    public $name;

    /**
     * @var string 接收参数,非必须：说明
     */
    public $desc;

    /**
     * @var int 接收参数,排序
     */
    public $sort = 0;

    /**
     * @var array 接收参数,上级id
     */
    public $parent_ids;


    /**
     * @inheritDoc
     */
    public function rules()
    {
        return ArrayHelper::merge([
            [['id', 'name'], 'required'],
            [['parent_ids'], 'array', 'type' => 'string'],
            [['parent_ids'],'parentRule'],
        ], parent::rules());
    }


    public function parentRule()
    {
        $self_child = HpRabcAdv::getDepartAllChildren($this->id, $this->external);
        $self_child[] = $this->id;

        // 检查多个部门的从属关系
        foreach ($this->parent_ids as $pid) {
            if (in_array($pid, $self_child)) {
                return '不能将下级部门作为上级';
            }
            $children = HpRabcAdv::getDepartAllChildren($pid, $this->external);
            foreach ($this->parent_ids as $pid2) {
                if (in_array($pid2, $children)) {
                    return '多个上级部门之间不能存在从属关系';
                }
            }
        }
        return true;
    }


    /**
     * @inheritDoc
     */
    public function run()
    {
        $db = $this->external->getDb();

        $db->beginTrans();

        try {

            $db->update($this->external->departTableName())
                ->cols([
                    'name' => $this->name,
                    'desc' => $this->desc,
                    'sort' => $this->sort,
                ])
                ->whereArray(['id' => $this->id])
                ->query();

            // 删除以前的关联关系
            $db->update($this->external->departRelationTableName())
                ->col('del_time', time())
                ->whereArray(['depart_id' => $this->id])
                ->query();

            // 重新插入
            if (empty($this->parent_ids)) {
                $this->parent_ids = [''];
            }
            foreach ($this->parent_ids as $pid) {
                $db->insert($this->external->departRelationTableName())
                    ->cols([
                        'id' => QTTX::$app->snowflake->id(),
                        'depart_id' => $this->id,
                        'parent_id' => $pid,
                        'del_time' => 0
                    ])
                    ->query();
            }

            $db->commitTrans();

            return true;

        } catch (\Exception $exception) {
            $db->rollBackTrans();
            throw $exception;
        }
    }
}
