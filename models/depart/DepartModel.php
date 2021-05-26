<?php
/**
 * File Name: DepartModel.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/25 4:53 下午
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
use qttx\web\ServiceModel;

/**
 * Class DepartModel
 * @package qh4module\rabc_adv\models\depart
 * @property ExtRabcAdv $external
 */
class DepartModel extends ServiceModel
{
    /**
     * {@inheritDoc}
     */
    public function rules()
    {
        return [
            [['id', 'create_by'], 'string', ['max' => 64]],
            [['name'], 'string', ['max' => 100]],
            [['create_time', 'sort', 'del_time'], 'integer'],
            [['desc'], 'string', ['max' => 200]]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function attributeLangs()
    {
        return [
            'id' => 'id',
            'name' => '部门名称',
            'create_by' => '创建人',
            'create_time' => '创建时间',
            'desc' => '说明',
            'sort' => '排序',
            'del_time' => 'delTime'
        ];
    }
}