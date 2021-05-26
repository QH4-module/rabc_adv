<?php
/**
 * File Name: CascaderData.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/25 10:12 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_adv\models\depart;


use qh4module\rabc_adv\external\ExtRabcAdv;
use qh4module\rabc_adv\HpRabcAdv;
use qh4module\token\TokenFilter;
use qttx\web\ServiceModel;

/**
 * Class CascaderData
 * @package qh4module\rabc_adv\models\depart
 * @property ExtRabcAdv $external
 */
class CascaderData extends ServiceModel
{
    /**
     * @var bool 是否只允许自己所关联的和下属角色选中
     */
    public $only_own_enable;

    /**
     * @var bool 是否只允许自己下属角色选中
     * 只有 only_own_enable 为 true ,该参数才有效
     */
    public $only_children_enable;

    /**
     * @inheritDoc
     */
    public function run()
    {

        $user_id = TokenFilter::getPayload('user_id');

        // 存储允许选择的id
        $enable_depart_ids = [];
        if ($this->only_own_enable) {
            list($depart_ids, $children_ids) = HpRabcAdv::getUserRelatedAllDepart($user_id, $this->external);
            if ($this->only_children_enable) {
                $enable_depart_ids = $children_ids;
            } else {
                $enable_depart_ids = array_merge($depart_ids, $children_ids);
            }
            if (empty($enable_depart_ids)) return [];
        }


        $tb_depart = $this->external->departTableName();
        $tb_rel = $this->external->departRelationTableName();

        $sql = $this->external->getDb()
            ->select(['id as value', 'name as label'])
            ->from($tb_depart);
//        if ($this->only_own_enable) {
//            $sql->whereIn('id', $enable_depart_ids);
//        }
        $result_depart = $sql->where('del_time=0')
            ->query();

        $sql = $this->external->getDb()
            ->select(['depart_id', 'parent_id'])
            ->from("$tb_rel as t1")
            ->leftJoin("$tb_depart as t2", 't1.depart_id=t2.id');
//        if ($this->only_own_enable) {
//            $sql->whereIn('depart_id', $enable_depart_ids);
//        }
        $result_relation = $sql->where('t1.del_time=0')
            ->orderByDESC(['t2.sort'])
            ->query();

        return Index::formatResult($result_depart, $result_relation, 'value');
    }
}