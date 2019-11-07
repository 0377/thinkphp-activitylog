<?php

namespace ice\activitylog\Models;

use think\db\Query;
use think\helper\Arr;
use think\Collection;
use ice\activitylog\Contracts\Activity as ActivityContract;
use think\Model;
use think\model\relation\MorphTo;

class Activity extends Model implements ActivityContract
{
    protected $type = [
        'properties' => 'serialize',
    ];
    protected $autoWriteTimestamp = true;

    public function __construct(array $data = [])
    {
        if (!isset($this->table)) {
            $this->name = config('activitylog.table_name');
        }
        parent::__construct($data);
    }

    public function subject(): MorphTo
    {
        if (config('activitylog.subject_returns_soft_deleted_models')) {
            return $this->morphTo();
        }

        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function getExtraProperty(string $propertyName)
    {
        return Arr::get($this->properties->toArray(), $propertyName);
    }

    public function changes(): Collection
    {
        if (!$this->properties instanceof Collection) {
            return new Collection();
        }

        return $this->properties->only(['attributes', 'old']);
    }

    public function getChangesAttr($value, $data): Collection
    {
        return $this->changes();
    }

    public function scopeInLog(Query $query, ...$logNames): Query
    {
        if (is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy(Query $query, Model $causer): Query
    {
        return $query
            ->where('causer_type', get_class($causer))
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Query $query, Model $subject): Query
    {
        return $query
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }
}
