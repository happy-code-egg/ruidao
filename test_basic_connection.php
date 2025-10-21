<?php

// 测试基本连接
echo "Testing basic Laravel connection...\n";

$urls = [
    'http://127.0.0.1:8000',
    'http://localhost:8000'
];

foreach ($urls as $baseUrl) {
    echo "Trying $baseUrl...\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 5
        ]
    ]);
    
    $result = @file_get_contents($baseUrl, false, $context);
    if ($result !== false) {
        echo "Connection successful to $baseUrl\n";
        echo "Response length: " . strlen($result) . " bytes\n";
        break;
    } else {
        echo "Failed to connect to $baseUrl\n";
    }
}

echo "Connection test completed.\n";
