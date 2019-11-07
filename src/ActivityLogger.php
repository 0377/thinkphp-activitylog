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

use think\helper\Str;
use think\helper\Arr;
use think\Model;
use ice\activitylog\Exceptions\CouldNotLogActivity;
use ice\activitylog\Contracts\Activity as ActivityContract;

class ActivityLogger
{

    protected $defaultLogName = '';


    /** @var ActivityLogStatus */
    protected $logStatus;

    /** @var ActivityContract */
    protected $activity;

    public function __construct(ActivityLogStatus $logStatus)
    {

        $this->defaultLogName = config('activitylog.default_log_name');

        $this->logStatus = $logStatus;
    }

    public function setLogStatus(ActivityLogStatus $logStatus)
    {
        $this->logStatus = $logStatus;

        return $this;
    }

    public function performedOn(Model $model)
    {
        $this->getActivity()->subject()->associate($model);

        return $this;
    }

    public function on(Model $model)
    {
        return $this->performedOn($model);
    }

    public function causedBy($modelOrId = null)
    {
        if ($modelOrId === null) {
            return $this;
        }

        $model = $this->normalizeCauser($modelOrId);

        $this->getActivity()->causer()->associate($model);

        return $this;
    }

    public function by($modelOrId)
    {
        return $this->causedBy($modelOrId);
    }

    public function causedByAnonymous()
    {
        $this->activity->causer_id = null;
        $this->activity->causer_type = null;

        return $this;
    }

    public function byAnonymous()
    {
        return $this->causedByAnonymous();
    }

    public function withProperties($properties)
    {
        $this->getActivity()->properties = collect($properties);

        return $this;
    }

    public function withProperty(string $key, $value)
    {
        $this->getActivity()->properties = $this->getActivity()->properties->put($key, $value);

        return $this;
    }

    public function useLog(string $logName)
    {
        $this->getActivity()->log_name = $logName;

        return $this;
    }

    public function inLog(string $logName)
    {
        return $this->useLog($logName);
    }

    public function tap(callable $callback, string $eventName = null)
    {
        call_user_func($callback, $this->getActivity(), $eventName);

        return $this;
    }

    public function enableLogging()
    {
        $this->logStatus->enable();

        return $this;
    }

    public function disableLogging()
    {
        $this->logStatus->disable();

        return $this;
    }

    public function log(string $description)
    {
        if ($this->logStatus->disabled()) {
            return;
        }

        $activity = $this->activity;
        $activity->description = $this->replacePlaceholders(
            $activity->description ?? $description,
            $activity
        );

        $activity->save();

        $this->activity = null;

        return $activity;
    }

    protected function normalizeCauser($modelOrId): Model
    {
        if ($modelOrId instanceof Model) {
            return $modelOrId;
        }

        throw CouldNotLogActivity::couldNotDetermineUser($modelOrId);
    }

    protected function replacePlaceholders(string $description, ActivityContract $activity): string
    {
        return preg_replace_callback('/:[a-z0-9._-]+/i', function ($match) use ($activity) {
            $match = $match[0];

            $attribute = (string)(new Str($match))->between(':', '.');

            if (!in_array($attribute, ['subject', 'causer', 'properties'])) {
                return $match;
            }

            $propertyName = substr($match, strpos($match, '.') + 1);

            $attributeValue = $activity->$attribute;

            if (is_null($attributeValue)) {
                return $match;
            }

            $attributeValue = $attributeValue->toArray();

            return Arr::get($attributeValue, $propertyName, $match);
        }, $description);
    }

    protected function getActivity(): ActivityContract
    {
        if (!$this->activity instanceof ActivityContract) {
            $this->activity = ActivitylogService::getActivityModelInstance();
            $this->useLog($this->defaultLogName)
                ->withProperties([])
                ->causedBy();
        }

        return $this->activity;
    }
}
