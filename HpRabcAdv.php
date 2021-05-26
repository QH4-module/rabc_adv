<?php
/**
 * File Name: HpRabcAdv.php
 * ©2020 All right reserved Qiaotongtianxia Network Technology Co., Ltd.
 * @author: hyunsu
 * @date: 2021/5/21 10:34 上午
 * @email: hyunsu@foxmail.com
 * @description:
 * @version: 1.0.0
 * ============================= 版本修正历史记录 ==========================
 * 版 本:          修改时间:          修改人:
 * 修改内容:
 *      //
 */

namespace qh4module\rabc_adv;


use qh4module\rabc_adv\external\ExtRabcAdv;
use qh4module\rabc_adv\models\RabcRedis;
use qh4module\token\TokenFilter;
use qttx\components\db\DbModel;
use qttx\web\External;

class HpRabcAdv
{
    /**
     * 从redis中删除用户信息
     * 个人用户的信息,Rabc内部会自动维护
     * 如果程序中手动修改了用户信息,请手动删除或者修改缓存
     * @param $user_id
     * @param ExtRabcAdv $external
     */
    public static function delRedisUserInfo($user_id, ExtRabcAdv $external = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if ($redis = $external->getRedis()) {
            $key = RabcRedis::user_info($user_id);
            $redis->del($key);
        }
    }

    /**
     * 获取用户的直属部门
     * @param string|array $user_id 用户id或数组
     * @param false|array $map 指定只获取部门id还是获取部门多个字段
     *                    参数为 false ,返回一维数组,表示所有相关部门的id
     *                    还可以传入一个键值对数组,键表示 depart 表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcAdv|null $external
     * @param null $db
     * @return array|mixed|string
     */
    public static function getUserRelatedDepart($user_id = null, $map = false, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = self::formatMap2Field($map);
            $sql = $db->select($select)
                ->from($external->relUserDepartTableName() . ' as ta')
                ->leftJoin($external->departTableName() . ' as tb', 'ta.depart_id=tb.id');
            if (is_array($user_id)) {
                $sql->whereIn('ta.user_id', $user_id);
            } else {
                $sql->whereArray(['ta.user_id' => $user_id]);
            }
            $result = $sql->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $sql = $db->select('depart_id')
                ->from($external->relUserDepartTableName());
            if (is_array($user_id)) {
                $sql->whereIn('user_id', $user_id);
            } else {
                $sql->whereArray(['user_id' => $user_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }

    /**
     * 获取用户的所有关联部门,包括下级
     * @param string|array $user_id
     * @param ExtRabcAdv|null $external
     * @param null $db
     * @return array 二维数组[直接关联部门id[],下级部门id[]]
     */
    public static function getUserRelatedAllDepart($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        $parent_ids = self::getUserRelatedDepart($user_id, false, $external, $db);
        if(empty($parent_ids)) return [[],[]];
        $result = self::getDepartAllChildren($parent_ids, $external, $db);
        return array($parent_ids, $result ?: []);
    }


    /**
     * 获取部门所有的下级,不包括部门本身
     * @param string|array $depart_id 部门id或id数组
     * @param ExtRabcAdv|null $external
     * @param null $db
     * @return array
     */
    public static function getDepartAllChildren($depart_id, ExtRabcAdv $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        $result = $db->select(['depart_id', 'parent_id'])
            ->from($external->departRelationTableName())
            ->where('del_time=0')
            ->query();

        if (empty($result)) return [];

        return self::_get_depart_children($result, $depart_id);
    }

    protected static function _get_depart_children($result, $parent)
    {
        $ary = [];
        foreach ($result as $item) {
            if (is_array($parent)) {
                if (in_array($item['parent_id'], $parent)) {
                    $ary[] = $item['depart_id'];
                    $tmp = self::_get_depart_children($result, $item['depart_id']);
                    if ($tmp) {
                        $ary = array_merge($ary, $tmp);
                    }
                }
            } else {
                if ($item['parent_id'] == $parent) {
                    $ary[] = $item['depart_id'];
                    $tmp = self::_get_depart_children($result, $item['depart_id']);
                    if ($tmp) {
                        $ary = array_merge($ary, $tmp);
                    }
                }
            }
        }
        return $ary;
    }

    /**
     * 获取部门的上级
     * @param string $depart_id 部门id
     * @param false|array $map 指定只获取部门id还是获取上级部门的多个字段
     *                    参数为 false ,返回一维数组,表示所有相关部门的id
     *                    还可以传入一个键值对数组,键表示 depart 表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcAdv|null $external
     * @param null $db
     * @return array|mixed|string
     */
    public static function getDepartParent($depart_id, $map = false, ExtRabcAdv $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = self::formatMap2Field($map);
            $sql = $db->select($select)
                ->from($external->departRelationTableName() . ' as ta')
                ->innerJoin($external->departTableName() . ' as tb', 'ta.parent_id=tb.id');
            if (is_array($depart_id)) {
                $sql->whereIn('ta.depart_id', $depart_id);
            } else {
                $sql->whereArray(['ta.depart_id' => $depart_id]);
            }
            $result = $sql->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $sql = $db->select('parent_id')
                ->from($external->departRelationTableName());
            if (is_array($depart_id)) {
                $sql->whereIn('depart_id', $depart_id);
            } else {
                $sql->whereArray(['depart_id' => $depart_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }

    /**
     * 获取指定用户是否是管理员
     * @param string $user_id
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return bool
     */
    public static function is_administrator($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();

        $result = $db->select('id')
            ->from($external->relUserRoleTableName())
            ->whereArray([
                'user_id' => $user_id,
                'role_id' => '1'
            ])
            ->where('del_time=0')
            ->row();

        return !empty($result);
    }

    /**
     * 获取所有的下级用户,不包括自己
     * @param string $user_id
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserAllChildren($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        list($depart_ids, $children_depart_ids) = self::getUserRelatedAllDepart($user_id, $external, $db);
        if (empty($children_depart_ids)) return [];
        $result = $db->select('user_id')
            ->from($external->relUserDepartTableName())
            ->whereIn('depart_id', $children_depart_ids)
            ->where('del_time=0')
            ->column();
        return $result ?: [];
    }

    /**
     * 获取用户关联的所有权限
     * @param string $user_id
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedPrivileges($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [];
        $result = self::getRoleRelatedPrivileges($role_ids, false, $external, $db);
        return $result ?: [];
    }

    /**
     * 获取用户关联的权限 key_path
     * @param string $user_id
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedPrivKeys($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [];
        $result = self::getRoleRelatedPrivileges($role_ids, ['key_path' => null,], $external, $db);
        if (empty($result)) return [];
        return array_column($result, 'key_path');
    }


    /**
     * 获取角色关联的权限
     * @param string|array $role_id 角色id或者角色id数组
     * @param false|array $map 指定只获取权限id还是获取权限多个字段
     *                    参数为 false ,返回一维数组,表示所有相关权限的id
     *                    还可以传入一个键值对数组,键表示 privilege 表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getRoleRelatedPrivileges($role_id, $map = false, ExtRabcAdv $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = self::formatMap2Field($map);
            $sql = $db->select($select)
                ->from($external->relRolePrivTableName() . ' as ta')
                ->leftJoin($external->privilegeTableName() . ' as tb', 'ta.privilege_id=tb.id');
            if (is_array($role_id)) {
                $sql->whereIn('ta.role_id', $role_id);
            } else {
                $sql->whereArray(['ta.role_id' => $role_id]);
            }
            $result = $sql->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $sql = $db->select('privilege_id')
                ->from($external->relRolePrivTableName());
            if (is_array($role_id)) {
                $sql->whereIn('role_id', $role_id);
            } else {
                $sql->whereArray(['role_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }


    /**
     * 获取用户直接关联的角色
     * @param string $user_id 用户的id
     * @param false|array $map 指定只获取角色id还是获取角色多个字段
     *                    参数为 false ,返回一维数组,表示所有相关角色的id
     *                    还可以传入一个键值对数组,键表示 role表的字段名,值表示别名(空值表示不取别名)
     *                    例如:[id=>value,name=>label]
     *                    返回 [[value=>xxxx,name=>xxxx],...] 的格式
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getUserRelatedRoles($user_id = null, $map = false, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        if (is_array($map)) {
            $select = self::formatMap2Field($map);
            $result = $db->select($select)
                ->from($external->relUserRoleTableName() . ' as ta')
                ->leftJoin($external->roleTableName() . ' as tb', 'ta.role_id=tb.id')
                ->whereArray(['ta.user_id' => $user_id])
                ->where('ta.del_time=0')
                ->query();

            return $result ?: [];
        } else {
            $result = $db->select('role_id')
                ->from($external->relUserRoleTableName())
                ->whereArray(['user_id' => $user_id])
                ->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }

    /**
     * 获取用户的所有关联角色,包括下级角色
     * @param string $user_id
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array 二维数组[直接关联角色id[],下级角色id[]]
     */
    public static function getUserRelatedAllRoles($user_id = null, ExtRabcAdv $external = null, $db = null)
    {
        if (empty($user_id)) $user_id = TokenFilter::getPayload('user_id');
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();
        $role_ids = self::getUserRelatedRoles($user_id, false, $external, $db);
        if (empty($role_ids)) return [[], []];
        $children_ids = self::getRoleAllChildren($role_ids, false, $external, $db);
        return [$role_ids, $children_ids ?: []];
    }


    /**
     * 返回所有的下级角色
     * @param string|array $role_id 角色id,可以是id数组
     * @param bool $map 指定只获取角色id还是包含层级关系
     *                  true 返回值是二维数组,包括
     *                      [
     *                          [role_id,asc_level,desc_level],
     *                          [role_id,asc_level,desc_level]
     *                          ...
     *                      ]
     *                  false 返回一维数组,[id1,id2,id3.....]
     * @param ExtRabcAdv|null $external
     * @param DbModel $db
     * @return array
     */
    public static function getRoleAllChildren($role_id, $map = false, ExtRabcAdv $external = null, $db = null)
    {
        if (is_null($external)) $external = new ExtRabcAdv();
        if (is_null($db)) $db = $external->getDb();

        if (is_array($map)) {
            $sql = $db->select(['role_id', 'asc_level', 'desc_level'])
                ->from($external->roleMoreTableName());
            if (is_array($role_id)) {
                $sql->whereIn('parent_id', $role_id);
            } else {
                $sql->whereArray(['parent_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->query();
            return $result ?: [];
        } else {
            $sql = $db->select('role_id')
                ->from($external->roleMoreTableName());
            if (is_array($role_id)) {
                $sql->whereIn('parent_id', $role_id);
            } else {
                $sql->whereArray(['parent_id' => $role_id]);
            }
            $result = $sql->where('del_time=0')
                ->column();
            return $result ?: [];
        }
    }

    /**
     * 将map参数转成 select 的字段
     * @param $map
     * @param string $tb_as 数据表别名
     * @return array
     */
    private static function formatMap2Field($map, $tb_as = 'tb')
    {
        $select = [];
        foreach ($map as $field => $alias) {
            if ($alias) {
                $select[] = "`$tb_as`.`{$field}` as {$alias}";
            } else {
                $select[] = "`$tb_as`.`{$field}`";
            }
        }
        return $select;
    }

}