The application uses Laravel's default logging stack backed by Monolog. The configuration in `config/logging.php` defines a `stack` channel that composes one or more underlying channels (`single`, `daily`, `slack`, `papertrail`, `stderr`, `syslog`, `errorlog`, `null`, `emergency`). The default channel is `stack`, the default level is `debug`, and the single/daily sinks write to `storage/logs/laravel.log`. Deprecation logging is disabled (`LOG_DEPRECATIONS_CHANNEL=null`).

No application code was found using the logger facade (`Log::`) or the `logger()` helper anywhere in the PHP source tree — there are no explicit log statements in controllers, models, services, middleware, or providers. All runtime logs therefore come from Laravel framework internals (request lifecycle, query logging if enabled, exception handling) rather than domain-specific instrumentation.

Key implications:
- Log output is controlled entirely through environment variables (`LOG_CHANNEL`, `LOG_STACK`, `LOG_LEVEL`, `LOG_DAILY_DAYS`, etc.).
- There is no custom formatter, processor, or dedicated PSR-3 logger service registered beyond what Laravel ships.
- Structured fields, correlation IDs, request-scoped context, or per-feature channels are not implemented.