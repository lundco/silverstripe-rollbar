# Usage

In your project's `_config.php`, set the following

    SS_Log::add_writer(\silverstripe\rollbar\writer\RollbarLogWriter::factory(), SS_Log::WARN, '<=');

## Set extras

Setting-up everything you think you want to send, all from one spot in `_config.php` is somewhat inflexible. Using the following however,
we can set additional, arbitrary and context-specific data to be sent to Rollbar via calls to `SS_Log::log()` and its optional
3rd parameter. You can then call `SS_Log::log()` in your project's customised Exception classes.

In order to inject additional data into a message at runtime, simply pass a 2-dimensional array
to the 3rd parameter of `SS_Log::log()` who's first key is "extra" and who's value is an array of values
comprising the data you wish to send:

    SS_Log::log('Help, my curry is too hot. I only asked for mild.', SS_Log::ERR, ['extra' => ['toilet' => 'now']]);
