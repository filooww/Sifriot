# Story: Automatic Background Processing
**Date**: 2026-02-15
**Context**: User requested "keep queue working" automatically without running a separate command.

## Problem
Laravel uses a queue system (database/redis/etc.) to handle long-running tasks like bulk file scanning. By default, this requires a separate worker process (`php artisan queue:work`) to be running.
- In a development environment like Laragon, users often forget to start this worker.
- This leads to tasks getting stuck in "Pending" status indefinitely.

## Solution
We have switched the default `QUEUE_CONNECTION` in `.env` from `database` to `sync`.

### How it works
- **Sync Driver**: Executes jobs immediately (synchronously) within the HTTP request lifecycle.
- **Benefit**: No separate worker process is needed. Features "just work".
- **Trade-off**: The browser will "spin" or look frozen while the task is running. If the task takes too long (> 60s), it may hit the PHP execution time limit.

## Implementation
1.  Updated `.env`: `QUEUE_CONNECTION=sync`
2.  Updated Project Documentation to reflect this behavior.

## Future Considerations
For production or very large datasets (> 1000 files), we should switch back to `database` or `redis` queues and use a process monitor (Supervisor) to keep the worker running, as synchronous processing will timeout.
