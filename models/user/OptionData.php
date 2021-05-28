<?php
/**
 * File Name: OptionData.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/27 10:16 下午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_adv\models\user;


use qh4module\rabc_adv\HpRabcAdv;
use qh4module\token\TokenFilter;

/**
 * Class OptionData
 * @package qh4module\rabc_adv\models\user
 */
class OptionData extends UserModel
{
    /**
     * @var bool 是否只允许自己和下级用户
     */
    public $only_own_enable;

    /**
     * @var bool 是否只允许下级用户
     * 只有 only_own_enable 为 true ,该参数才有效
     */
    public $only_children_enable;


    public function run()
    {
        $user_id = TokenFilter::getPayload('user_id');

        // 存储允许选择的id
        $enable_user_ids = [];
        if ($this->only_own_enable) {
            $children_ids = HpRabcAdv::getUserAllChildren($user_id, $this->external);
            $enable_user_ids = $children_ids;
            if (!$this->only_children_enable) {
                $enable_user_ids[] = $user_id;
            }
        }

        $tb_user = $this->external->userTableName();
        $tb_info = $this->external->userInfoTableName();
        $sql = $this->external->getDb()
            ->select(['id', 'account', 'id as value', 'avatar', 'nick_name', 'nick_name as label', 'gender'])
            ->from("$tb_user as t1")
            ->leftJoin("$tb_info as t2", 't1.id=t2.user_id');
        if ($this->only_own_enable) {
            if (empty($enable_role_ids)) return [];
            $sql->whereIn('t1.id', $enable_user_ids);
        }
        $result = $sql->where("t1.del_time=0")
            ->query();

        return $result ?: [];
    }
}