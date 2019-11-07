<?php
/**
 * *
 *  * Created by PhpStorm.
 *  * User: ice
 *  * Email: ice@sbing.vip
 *  * Site: https://sbing.vip
 *  * Date: 2019/11/7 下午3:15
 *  * Time: $time
 *
 */

use ice\activitylog\ActivityLogger;
use ice\activitylog\ActivityLogStatus;

if (!function_exists('activity')) {
    function activity(string $logName = null): ActivityLogger
    {
        $defaultLogName = config('activitylog.default_log_name');

        $logStatus = app(ActivityLogStatus::class);

        return app(ActivityLogger::class)
            ->useLog($logName ?? $defaultLogName)
            ->setLogStatus($logStatus);
    }
}
