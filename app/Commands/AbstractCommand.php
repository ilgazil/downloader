<?php

namespace App\Commands;

use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
    public function handle(): int
    {
        try {
            $this->_handle();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }

    protected abstract function _handle();
}
