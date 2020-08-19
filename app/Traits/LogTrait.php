<?php

namespace App\Traits;

use Log;

/**
* Traits for writing custom log
*/
trait LogTrait
{
    public function logError($module, $identity, $error, $extra = [])
    {
        $error = explode("\n", $error, 2);
        $first_line_error = $error[0];

        $error_log = [
            'module' => $module,
            'identity' => $identity,
            'error' => $first_line_error
        ];

        if (! empty($extra)) {
            $error_log += $extra;
        }

        Log::error($error_log);
    }

}
