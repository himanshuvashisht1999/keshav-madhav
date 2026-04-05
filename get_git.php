<?php
file_put_contents('git_log_clean.txt', shell_exec('git log -p resources/views/admin/packing/process.blade.php'));
