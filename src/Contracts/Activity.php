<?php
/**
 * *
 *  * Created by PhpStorm.
 *  * User: shibing
 *  * ============================================================================
 *  * 版权所有 2018-2020 南阳市微企胜网络科技有限公司，并保留所有权利。
 *  * 网站地址: http://www.weiqisheng.cn；
 *  * ----------------------------------------------------------------------------
 *  * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 *  * 使用；不允许对程序代码以任何形式任何目的的再发布。
 *  * ============================================================================
 *  * Date: 2019/11/7 下午3:10
 *  * Time: $time
 *
 */

namespace ice\activitylog\Contracts;

use think\Model;
use think\db\Query;
use think\Collection;
use think\model\relation\MorphTo;

interface Activity
{
    public function subject(): MorphTo;

    public function causer(): MorphTo;

    public function getExtraProperty(string $propertyName);

    public function changes(): Collection;

    public function scopeInLog(Query $query, ...$logNames): Query;

    public function scopeCausedBy(Query $query, Model $causer): Query;

    public function scopeForSubject(Query $query, Model $subject): Query;
}
