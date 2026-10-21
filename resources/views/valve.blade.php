<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Red Lion Control</title>
</head>
<body>
    <h1>Red Lion Controls</h1>
    <h2>Input ranges from 400 to 2000</h2>
    <p>400 is closed, 2000 is open</p>

    <form action="/submit" method="POST">
        @csrf
        <label for="value">Value to send</label>
        <input
            type="number"
            id="value"
            name="value"
            value="{{ old('value') }}"
            min="400"
            max="2000"
            step="1"
            required
        >
        <button type="submit">Send</button>
    </form>

    @error('value')
        <p>{{ $message }}</p>
    @enderror

    <h2>Submission History</h2>
    <p>The highlighted row is the latest submission, not a success indicator.</p>

    <table>
        <thead>
            <tr>
                <th>Entered Value</th>
                <th>mA</th>
                <th>Requested Opening</th>
                <th>Submission time</th>
                <th>Outcome</th>
            </tr>
        </thead>
        <tbody>
            @forelse (session('submissions', []) as $submission)
                <tr @if ($loop->last) style="background-color: #fff3cd;" @endif>
                    <td>{{ $submission['value'] }}</td>
                    <td>{{ number_format($submission['value'] / 100, 2) }} mA</td>
                    <td>{{ number_format(($submission['value'] - 400) / 1600 * 100, 2) }}%</td>
                    <td>{{ $submission['submitted_at'] }}</td>
                    <td>{{ $submission['outcome'] }}</td>
                </tr>
            @empty

                <tr>
                    <td colspan="5">No submissions yet.</td>
                </tr>

            @endforelse
        </tbody>
    </table>

</body>
</html>