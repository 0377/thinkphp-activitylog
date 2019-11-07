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

namespace ice\activitylog\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Spatie\Activitylog\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\ActivitylogServiceProvider;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait LogsActivity
{
    use DetectsChanges;

    protected $enableLoggingModelsEvents = true;

    protected static function bootLogsActivity()
    {
        static::eventsToBeRecorded()->each(function ($eventName) {
            return static::$eventName(function (Model $model) use ($eventName) {
                if (! $model->shouldLogEvent($eventName)) {
                    return;
                }

                $description = $model->getDescriptionForEvent($eventName);

                $logName = $model->getLogNameToUse($eventName);

                if ($description == '') {
                    return;
                }

                $attrs = $model->attributeValuesToBeLogged($eventName);

                if ($model->isLogEmpty($attrs) && ! $model->shouldSubmitEmptyLogs()) {
                    return;
                }

                $logger = app(ActivityLogger::class)
                    ->useLog($logName)
                    ->performedOn($model)
                    ->withProperties($attrs);

                if (method_exists($model, 'tapActivity')) {
                    $logger->tap([$model, 'tapActivity'], $eventName);
                }

                $logger->log($description);
            });
        });
    }

    public function shouldSubmitEmptyLogs(): bool
    {
        return ! isset(static::$submitEmptyLogs) ? true : static::$submitEmptyLogs;
    }

    public function isLogEmpty($attrs): bool
    {
        return empty($attrs['attributes'] ?? []) && empty($attrs['old'] ?? []);
    }

    public function disableLogging()
    {
        $this->enableLoggingModelsEvents = false;

        return $this;
    }

    public function enableLogging()
    {
        $this->enableLoggingModelsEvents = true;

        return $this;
    }

    public function activities(): MorphMany
    {
        return $this->morphMany(ActivitylogServiceProvider::determineActivityModel(), 'subject');
    }

    public function getDescriptionForEvent(string $eventName): string
    {
        return $eventName;
    }

    public function getLogNameToUse(string $eventName = ''): string
    {
        if (isset(static::$logName)) {
            return static::$logName;
        }

        return config('activitylog.default_log_name');
    }

    /*
     * Get the event names that should be recorded.
     */
    protected static function eventsToBeRecorded(): Collection
    {
        if (isset(static::$recordEvents)) {
            return collect(static::$recordEvents);
        }

        $events = collect([
            'created',
            'updated',
            'deleted',
        ]);

        if (collect(class_uses_recursive(static::class))->contains(SoftDeletes::class)) {
            $events->push('restored');
        }

        return $events;
    }

    public function attributesToBeIgnored(): array
    {
        if (! isset(static::$ignoreChangedAttributes)) {
            return [];
        }

        return static::$ignoreChangedAttributes;
    }

    protected function shouldLogEvent(string $eventName): bool
    {
        if (! $this->enableLoggingModelsEvents) {
            return false;
        }

        if (! in_array($eventName, ['created', 'updated'])) {
            return true;
        }

        if (Arr::has($this->getDirty(), 'deleted_at')) {
            if ($this->getDirty()['deleted_at'] === null) {
                return false;
            }
        }

        //do not log update event if only ignored attributes are changed
        return (bool) count(Arr::except($this->getDirty(), $this->attributesToBeIgnored()));
    }
}
