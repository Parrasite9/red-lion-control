# Red Lion Control

A small standalone Laravel/Blade learning application for submitting an integer over Modbus TCP and displaying submission history. It is independent of Big D Monitor.

## Current behavior

- Accepts whole numbers from 400 through 2000, with browser controls and Laravel server validation.
- Sends the original integer using function 06 (write one holding register).
- Currently connects only to a local simulator at `127.0.0.1:5020`, unit ID `1`, protocol address `3`.
- Checks that the simulator acknowledges the write by echoing the request.
- Displays entered value, converted mA, requested opening percentage, time, and outcome in a Blade table.
- Keeps history in the browser's Laravel session across reloads, until the session expires. History is not shared between browsers or a permanent audit record.
- Highlights the latest row independently of success.

Examples: 400 = 4.00 mA / 0% requested opening; 1200 = 12.00 mA / 50%; 2000 = 20.00 mA / 100%. Conversion is for display only. An acknowledgement is not proof of physical pump movement.

## Local setup

Use PHP 8.4+ (the committed development dependencies require it), Composer, and SQLite support. The simulator uses Python 3.12 in the tested setup. Node is not needed for the current plain Blade page.

From a fresh clone:

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

Keep `.env`, the SQLite database, logs, sessions, and dependencies out of Git. The default environment uses SQLite for application storage and database-backed sessions.

### Start the simulator

From the project root, install the simulator in a sibling directory:

```bash
python3 -m venv ../red-lion-simulator-env
../red-lion-simulator-env/bin/pip install 'pymodbus[simulator]==3.16.1'
../red-lion-simulator-env/bin/pymodbus.simulator \
  --json_file modbus-simulator.json \
  --modbus_server server \
  --modbus_device device \
  --http_host 127.0.0.1 \
  --http_port 8081
```

Leave this terminal running. The selected configuration listens for Modbus on `127.0.0.1:5020` and exposes the simulator UI at http://127.0.0.1:8081. Register address 3 is writable. PyModbus 3.16.1 may print deprecation notices about its simulator context; these concern a future major version.

### Start Laravel

In another terminal, from the project root:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Open http://127.0.0.1:8000. Submit 1200, then 1600. Both rows should remain, with only the latest highlighted. These writes affect only the local simulator.

If a T3/AppImage environment causes Artisan to launch the wrong executable, the workaround used on the development machine is:

```bash
PHP_BINARY=/usr/bin/php8.4 php artisan serve --host=127.0.0.1 --port=8000
```

## Code map

- `routes/web.php`: form page and submission route.
- `app/Http/Controllers/ValveController.php`: validation, writer call, session history, redirect.
- `app/Services/ModbusWriter.php`: local TCP connection, request construction, response verification, cleanup.
- `resources/views/valve.blade.php`: input, errors, conversion display, and history table.
- `modbus-simulator.json`: local test device configuration, adapted from PyModbus's bundled simulator configuration.

## Verification and remaining work

Run the starter tests with:

```bash
php vendor/bin/phpunit
```

The starter tests are not comprehensive Modbus coverage. During development, validation cases were checked through local HTTP requests, and a simulator write of 1200 received a matching acknowledgement. The form-to-history workflow was also manually exercised.

This is a work in progress:

- Connection failures and timeouts currently raise exceptions; failed attempts do not yet reach history.
- Pending state and duplicate-submission prevention are not implemented. There are no automatic write retries.
- The displayed timestamp is currently captured after acknowledgement, not when the attempt begins.
- Authentication and shared deployment access controls are not implemented. Keep development servers bound to localhost.
- Real-device register mapping, write-function support, signedness, and server-side network reachability remain unverified. Protocol address 3 is provisional for documented register 40004.
- No real-device write has been performed. Real-device testing requires an explicit, agreed test before changing the simulator target.
