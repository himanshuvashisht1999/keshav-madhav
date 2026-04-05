<?php
file_put_contents('head.blade', shell_exec('git show HEAD:resources/views/admin/packing/process.blade.php'));
