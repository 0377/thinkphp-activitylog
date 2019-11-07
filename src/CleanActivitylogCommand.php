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
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\db\Query;
use think\console\Command;

class CleanActivitylogCommand extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('activitylog:clean')
            ->addArgument('log', Argument::OPTIONAL, '(optional) The log name that will be cleaned.', '')
            ->addOption('days', 'd', Option::VALUE_OPTIONAL,
                '(optional) Records older than this number of days will be cleaned.', '')
            ->setDescription('Clean up old records from the activity log.');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->comment('Cleaning activity log...');

        $log = $input->getArgument('log');

        $maxAgeInDays = $input->getOption('days') ?? config('activitylog.delete_records_older_than_days');

        $cutOffDate = Carbon::now()->subDays($maxAgeInDays)->format('Y-m-d H:i:s');

        $activity = ActivitylogService::getActivityModelInstance();

        $amountDeleted = $activity::where('created_at', '<', $cutOffDate)
            ->when($log !== null, function (Query $query) use ($log) {
                $query->inLog($log);
            })
            ->delete();

        $output->info("Deleted {$amountDeleted} record(s) from the activity log.");

        $output->comment('All done!');
    }
}
