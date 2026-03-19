<?php
// Permanent redirect to the real entrypoint
http_response_code(301);
header('Location: /public/');
exit;