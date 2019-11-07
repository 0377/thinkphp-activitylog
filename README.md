# Log activity inside your Thinkphp6 app

The `0377/thinkphp-activitylog` package provides easy to use functions to log the activities of the users of your app. It can also automatically log model events. 
The Package stores all activity in the `activity_log` table.

Here's a demo of how you can use it:

```php
activity()->log('Look, I logged something');
```

You can retrieve all activity using the `ice\activitylog\Models\Activity` model.

```php
Activity::select();
```

Here's a more advanced example:
```php
activity()
   ->performedOn($anEloquentModel)
   ->causedBy($user)
   ->withProperties(['customProperty' => 'customValue'])
   ->log('Look, I logged something');
   
$lastLoggedActivity = Activity::select()->last();

$lastLoggedActivity->subject; //returns an instance of an eloquent model
$lastLoggedActivity->causer; //returns an instance of your user model
$lastLoggedActivity->getExtraProperty('customProperty'); //returns 'customValue'
$lastLoggedActivity->description; //returns 'Look, I logged something'
```


Here's an example on.

```php
$newsItem->name = 'updated name';
$newsItem->save();

//updating the newsItem will cause the logging of an activity
$activity = Activity::all()->last();

$activity->description; //returns 'updated'
$activity->subject; //returns the instance of NewsItem that was saved
```

Calling `$activity->changes()` will return this array:

```php
[
   'attributes' => [
        'name' => 'updated name',
        'text' => 'Lorum',
    ],
    'old' => [
        'name' => 'original name',
        'text' => 'Lorum',
    ],
];
```


## Documentation
You'll find the documentation on [https://docs.spatie.be/laravel-activitylog](https://docs.spatie.be/laravel-activitylog).

Find yourself stuck using the package? Found a bug? Do you have general questions or suggestions for improving the activity log? Feel free to [create an issue on GitHub](https://github.com/spatie/laravel-activitylog/issues), we'll try to address it as soon as possible.

If you've found a security issue please mail [freek@spatie.be](mailto:freek@spatie.be) instead of using the issue tracker.


## Installation

You can install the package via composer:

``` bash
composer require ice/thinkphp-activitylog
```


This is the contents of the published config file:

```php
return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled'                        => Env::get('ACTIVITY_LOGGER_ENABLED', true),

    /*
     * When the clean-command is executed, all recording activities older than
     * the number of days specified here will be deleted.
     */
    'delete_records_older_than_days' => 365,

    /*
     * If no log name is passed to the activity() helper
     * we use this default log name.
     */
    'default_log_name'               => 'default',

    /*
     * This model will be used to log activity.
     * It should be implements the ice\activitylog\Contracts\Activity interface
     * and extend think\Model.
     */
    'activity_model'                 => \ice\activitylog\Models\Activity::class,

    /*
     * This is the name of the table that will be created by the migration and
     * used by the Activity model shipped with this package.
     */
    'table_name'                     => 'activity_log',

];
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
