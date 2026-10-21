<?php

namespace App\Http\Controllers;

use App\Services\ModbusWriter;
use Illuminate\Http\Request;

class ValveController extends Controller
{
    public function submit(Request $request)
    {
        $validation = $request->validate([
            'value' => 'required|integer|min:400|max:2000'
        ]);

        $value = $validation['value'];

        $createModbusWriter = new ModbusWriter();
        $newModbusWriterValues = $createModbusWriter->write(3, $value);

        $newModbusWriterValues['submitted_at'] = now()->format('Y-m-d H:i:s');

        $request->session()->push('submissions', $newModbusWriterValues);

        return redirect('/');
    }
}
