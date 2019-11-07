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

namespace ice\activitylog;

use Carbon\Carbon;
use think\db\Query;
use think\console\Command;

class CleanActivitylogCommand extends Command
{
    protected $signature = 'activitylog:clean
                            {log? : (optional) The log name that will be cleaned.}
                            {--days= : (optional) Records older than this number of days will be cleaned.}';

    protected $description = 'Clean up old records from the activity log.';

    public function handle()
    {
        $this->comment('Cleaning activity log...');

        $log = $this->argument('log');

        $maxAgeInDays = $this->option('days') ?? config('activitylog.delete_records_older_than_days');

        $cutOffDate = Carbon::now()->subDays($maxAgeInDays)->format('Y-m-d H:i:s');

        $activity = ActivitylogServiceProvider::getActivityModelInstance();

        $amountDeleted = $activity::where('created_at', '<', $cutOffDate)
            ->when($log !== null, function (Query $query) use ($log) {
                $query->inLog($log);
            })
            ->delete();

        $this->info("Deleted {$amountDeleted} record(s) from the activity log.");

        $this->comment('All done!');
    }
}
