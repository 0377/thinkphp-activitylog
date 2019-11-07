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

namespace ice\activitylog;

use think\Model;
use think\Service;
use ice\activitylog\Contracts\Activity;
use ice\activitylog\Exceptions\InvalidConfiguration;
use ice\activitylog\Models\Activity as ActivityModel;
use ice\activitylog\Contracts\Activity as ActivityContract;

class ActivitylogService extends Service
{
    public function boot()
    {

    }

    public function register()
    {
        $this->app->bind('command.activitylog:clean', CleanActivitylogCommand::class);

        $this->commands([
            'activitylog:clean' => CleanActivitylogCommand::class,
        ]);

        $this->app->bind(ActivityLogger::class);

        $this->app->bind(ActivityLogStatus::class);
    }

    public static function determineActivityModel(): string
    {
        $activityModel = config('activitylog.activity_model') ?? ActivityModel::class;

        if (!is_a($activityModel, Activity::class, true)
            || !is_a($activityModel, Model::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($activityModel);
        }

        return $activityModel;
    }

    public static function getActivityModelInstance(): ActivityContract
    {
        $activityModelClassName = self::determineActivityModel();

        return new $activityModelClassName();
    }
}
