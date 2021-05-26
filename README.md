QH4框架扩展模块-权限管理模块-高级版本

### 依赖
该模块依赖于城市模块,主要是将 user_info 表的 `city_id` 字段进行转换,不需要这个功能可以删除对应行 (models/user/detail 的54,59,65行)
```php
composer require qh4module/city
```

### 关于解析YML文件

该方法依赖于 `yaml` 扩展，最简单的安装方式
```
pecl install ymal
```
也可以使用其它方式编译安装，具体安装方式因个人环境而异。

在模块的 `test` 目录提供了一份示例

可以调用 `actionParseYml` 方法来解析文件，根据你自己的 controller 引用方式，可能需要手动传入可用的 Token

yml文件必须放在 `libs` 目录下，名称为 `privilege.yml`


### 功能
这个是高级版本的权限管理模块,有4层模型,包括用户、角色、权限、部门。

对应的有基础版本，有3层模型，包括用户、角色、权限。相对轻量简单

基础版本，使用角色，同时控制权限和数据显示。高级版本中，采用权限和数据分离的思想，角色仅用于控制权限，由部门控制用户显示的数据

举个例子：假设有一个项目列表，基础版角色同时控制用户能不能看到项目列表这个菜单，以及项目列表中都显示那些项目。
而在高级版本中，角色仅控制用户能不能看到项目列表这个菜单，部门控制了用户能看到那些项目

### 简述
* 用户: 后台管理账户,可以登录后台

* 角色: 中间层,用来关联用户和权限

* 权限(为了和权限管理模块的这个`权限`做区分,有时候也会写作权限资源): 实际操作或展示信息,包括菜单和页面上的按钮或显示信息等

* 部门：用来控制用户的显示的数据

用户与角色是多对多的关系,即: 一个用户可以有多个角色,一个角色可以分配给多个用户

角色与资源是多对多的关系,即: 一个角色可以有多种权限资源, 一种权限资源可以分配多个角色

用户与部门是多对都的关系，即：一个用户可以属于多个部门，一个部门也可以有多个用户

部门与部门之间为多继承关系，即：一个部门和可以同时属于两个部门，但是一个部门的多个上级部门之间不能存在从属关系。

用户之间不存在继承关系,用户的关系通过角色推断出来,用户所拥有的权限也需要通过角色来推断。用户多个角色之间权限取交集

角色是单继承关系,即一个角色有且只有一个上级

权限资源是单继承关系,即一个权限资源有且只有一个上级


### api列表
```php
/**
 * 解析权限 yml 文件
 * 文件需要放在 lib 目录下
 * @return array|mixed
 */
public function actionParseYml()
```

```php
/**
 * 获取主菜单数据
 * @return array
 */
public function actionMainMenu()
```

```php
/**
 * 获取用户所有权限的key,前端主要依赖这些值判定权限
 */
public function actionPrivilegeKeys()
```

```php
/**
 * 获取部门的级联数据树
 * @return array
 */
public function actionDepartCascaderData()
```

```php
/**
 * 获取权限的级联数据树
 * @return array
 */
public function actionPrivCascaderData()
```

```php
/**
 * 获取角色的级联数据树
 * @return array
 */
public function actionRoleCascaderData()
```

```php
/**
 * 获取权限资源列表,返回数据树
 * @return array
 */
public function actionPrivilegeIndex()
```

```php
/**
 * 新增一条权限资源
 * @return array
 */
public function actionPrivilegeCreate()
```

```php
/**
 * 获取权限资源详情
 * @return array
 */
public function actionPrivilegeDetail()
```

```php
/**
 * 更新权限资源
 * @return array
 */
public function actionPrivilegeUpdate()
```

```php
/**
 * 批量删除权限资源
 * @return array
 */
public function actionPrivilegeDelete()
```

```php
/**
 * 获取角色列表,返回数据树
 * @return array
 */
public function actionRoleIndex()
```

```php
/**
 * 新增一个角色
 * @return array
 */
public function actionRoleCreate()
```

```php
/**
 * 更新角色
 * @return array
 */
public function actionRoleUpdate()
```

```php
/**
 * 获取角色详情
 * @return array
 */
public function actionRoleDetail()
```

```php
/**
 * 批量删除角色
 * @return array
 */
public function actionRoleDelete()
```

```php
/**
 * 分页获取用户数据
 * @return array [total,list,page,limit]
 */
public function actionUserIndex()
```

```php
/**
 * 新增一个用户
 * @return array
 */
public function actionUserCreate()
```

```php
/**
 * 更新用户信息
 * @return array
 */
public function actionUserUpdate()
```

```php
/**
 * 获取用户详情
 * @return array
 */
public function actionUserDetail()
```

```php
/**
 * 批量删除用户
 * @return array
 */
public function actionUserDelete()
```


```php
/**
 * 获取部门列表,返回数据树
 * @return array
 */
public function actionDepartIndex()
```

```php
/**
 * 新增部门
 */
public function actionDepartCreate()
```

```php
/**
 * 更新部门
 */
public function actionDepartUpdate()
```

```php
/**
 * 批量删除部门
 */
public function actionDepartDelete()
```

```php
/**
 * 获取部门详情
 * @return array
 */
public function actionDepartDetail()
```

### 方法列表
```php
/**
 * 获取指定用户是否是管理员
 * @param string $user_id
 * @param External|null $external
 * @param DbModel $db
 * @return bool
 */
public static function is_administrator($user_id=null,External $external=null,$db = null)
```

```php
/**
 * 获取所有的下级用户,不包括自己
 * @param string $user_id
 * @param ExtRabcAdv|null $external
 * @param DbModel $db
 * @return array
 */
public static function getUserAllChildren($user_id = null, ExtRabcAdv $external = null, $db = null)
```

```php
/**
 * 获取用户关联的所有权限
 * @param string $user_id
 * @param ExtRabcAdv|null $external
 * @param DbModel $db
 * @return array
 */
public static function getUserRelatedPrivileges($user_id = null, ExtRabcAdv $external = null, $db = null)
```

```php
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
```

```php
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
```

```php
/**
 * 获取用户的所有关联角色,包括下级角色
 * @param string $user_id
 * @param ExtRabcAdv|null $external
 * @param DbModel $db
 * @return array 二维数组[直接关联角色id[],下级角色id[]]
 */
public static function getUserRelationAllRoles($user_id = null, ExtRabcAdv $external = null, $db = null)
```

```php
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
```

```php
/**
 * 获取用户关联的权限 key_path
 * @param string $user_id
 * @param ExtRabcAdv|null $external
 * @param DbModel $db
 * @return array
 */
public static function getUserRelatedPrivKeys($user_id = null, ExtRabcAdv $external = null, $db = null)
```


```php
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
```

```php
/**
 * 获取用户的所有关联部门,包括下级
 * @param string|array $user_id
 * @param ExtRabcAdv|null $external
 * @param null $db
 * @return array 二维数组[直接关联部门id[],下级部门id[]]
 */
public static function getUserRelatedAllDepart($user_id = null, ExtRabcAdv $external = null, $db = null)
```

```php
/**
 * 获取部门所有的下级,不包括部门本身
 * @param string|array $depart_id 部门id或id数组
 * @param ExtRabcAdv|null $external
 * @param null $db
 * @return array
 */
public static function getDepartAllChildren($depart_id, ExtRabcAdv $external = null, $db = null)
```

```php
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
```