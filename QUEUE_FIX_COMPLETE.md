# Queue Job Dispatch Fix - Root Cause & Solution

## Problem Summary

Jobs were not appearing in the `jobs` database table when dispatched using Laravel's `dispatch()` helper method.

## Root Cause Analysis

### Issue 1: `dispatch()` helper doesn't work with database queue

The `dispatch()` helper function was not persisting jobs to the database when using the database queue driver. Testing revealed:

-   `dispatch(new Job)` → 0 jobs in table ❌
-   `Queue::push(new Job)` → Jobs persist ✅
-   `Bus::dispatch(new Job)` → Jobs persist ✅ (sometimes)
-   `Queue::connection('database')->push(new Job)` → Jobs persist ✅

**Root Cause**: Laravel's `dispatch()` helper has issues with database queue when combined with `after_commit=true` configuration.

### Issue 2: `after_commit=true` causes jobs to never persist

When `config/queue.php` had `'after_commit' => true`, jobs were held in a deferred queue waiting for a database transaction commit that never materialized in CLI context.

**Root Cause**: The `after_commit` option defers job dispatch until after database transaction commits. In CLI scripts without explicit transactions, this results in jobs never being actually queued.

### Issue 3: Test data type mismatch

Test jobs used string IDs (e.g., 'SIM0001') but the `idperson` column in the database is INTEGER, causing SQL type casting errors:

```
SQLSTATE[22007]: Invalid datetime format: 1292 Truncated incorrect INTEGER value: 'SIM0001'
```

## Solution Implemented

### 1. Fix Configuration (`config/queue.php`)

Set `after_commit` to `false` for database queue:

```php
'database' => [
    'driver' => 'database',
    'connection' => env('DB_QUEUE_CONNECTION'),
    'table' => env('DB_QUEUE_TABLE', 'jobs'),
    'queue' => env('DB_QUEUE', 'default'),
    'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
    'after_commit' => false,  // ← CHANGE THIS
],
```

### 2. Update Controller Dispatch Method

Changed `SyncPembayaranController::start()` to use `Queue::connection('database')->push()` instead of `dispatch()` helper:

**Before:**

```php
SyncPembayaranSiswaJob::dispatch($siswa->idperson);
```

**After:**

```php
$job = (new SyncPembayaranSiswaJob($siswa->idperson))->onQueue('sync-pembayaran');
Queue::connection('database')->push($job);
```

### 3. Added Queue Import

Added import to controller:

```php
use Illuminate\Support\Facades\Queue;
```

## Verification

### Test Results

✅ **10 real siswa jobs dispatched and processed successfully**

Jobs created with actual `idperson` integers:

```
Job created for siswa: 190013
Job created for siswa: 190015
Job created for siswa: 190026
...
```

Queue worker output:

```
2025-11-25 07:08:06 App\Jobs\SyncPembayaranSiswaJob RUNNING
2025-11-25 07:08:07 App\Jobs\SyncPembayaranSiswaJob 703.01ms DONE
2025-11-25 07:08:07 App\Jobs\SyncPembayaranSiswaJob RUNNING
2025-11-25 07:08:08 App\Jobs\SyncPembayaranSiswaJob 520.98ms DONE
... (all 10 jobs completed successfully)
```

### Data Verification

✅ Pembayaran data successfully saved to siswa records:

```
✓ Siswa 190013: Pembayaran saved (1 items)
✓ Siswa 190015: Pembayaran saved (1 items)
✓ Siswa 190026: Pembayaran saved (1 items)
✓ Siswa 190028: Pembayaran saved (1 items)
```

## Queue Worker Command

To process jobs in production:

```bash
# Single queue (recommended)
php artisan queue:work --queue=sync-pembayaran

# Or default queue
php artisan queue:work --queue=default

# With timeout and max jobs
php artisan queue:work --queue=default --max-jobs=100 --max-time=300

# As daemon (continuously running)
php artisan queue:work --daemon
```

## Configuration Summary

### .env Requirements

```env
QUEUE_CONNECTION=database
CACHE_STORE=database
DB_QUEUE_CONNECTION=mysql
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
```

### config/queue.php Database Connection

```php
'database' => [
    'driver' => 'database',
    'connection' => 'mysql',
    'table' => 'jobs',
    'queue' => 'default',
    'retry_after' => 90,
    'after_commit' => false,
],
```

## Files Modified

1. `config/queue.php` - Set `after_commit=false`
2. `app/Http/Controllers/Admin/SyncPembayaranController.php` - Use `Queue::connection()->push()` instead of `dispatch()`

## Testing Completed

✅ Jobs table persists jobs correctly
✅ Queue worker processes jobs successfully
✅ Pembayaran data synced to database
✅ Cache counters update properly
✅ Error handling works (max 3 retries)
✅ All 10 test jobs completed DONE status
