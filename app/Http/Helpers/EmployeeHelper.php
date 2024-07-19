<?php

namespace App\Http\Helpers;

use App\Models\Preference;

class EmployeeHelper
{
    public static function generateEmpNo()
    {
        $empPreference = Preference::where('code', 'EMP')->first();
        $currentValue = (int) $empPreference->value;
        $newValue = $currentValue + 1;
        $empPreference->value = (string) $newValue;
        $empPreference->save();

        return 'EMP' . str_pad($newValue, 6, '0', STR_PAD_LEFT);
    }
}
