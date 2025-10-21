<?php

// 测试分配管理API接口
$baseUrl = 'http://127.0.0.1:8018';

echo "Testing Assignment Management APIs...\n\n";

function testEndpoint($url, $method = 'GET', $data = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json'
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

// 1. 测试新申请列表
echo "1. Testing New Applications API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/new-applications?page=1&limit=10');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ New Applications API is working\n";
        echo "Total records: " . $data['data']['total'] . "\n";
        echo "Records count: " . count($data['data']['list']) . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}
echo "\n";

// 2. 测试中间案列表
echo "2. Testing Middle Cases API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/middle-cases?page=1&limit=10');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ Middle Cases API is working\n";
        echo "Total records: " . $data['data']['total'] . "\n";
        echo "Records count: " . count($data['data']['list']) . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}
echo "\n";

// 3. 测试科服案例列表
echo "3. Testing Tech Service Cases API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/tech-service-cases?page=1&limit=10');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ Tech Service Cases API is working\n";
        echo "Total records: " . $data['data']['total'] . "\n";
        echo "Records count: " . count($data['data']['list']) . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}
echo "\n";

// 4. 测试已分配案例列表
echo "4. Testing Assigned Cases API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/assigned-cases?page=1&limit=10');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ Assigned Cases API is working\n";
        echo "Total records: " . $data['data']['total'] . "\n";
        echo "Records count: " . count($data['data']['list']) . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}
echo "\n";

// 5. 测试可分配用户列表
echo "5. Testing Assignable Users API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/assignable-users');
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ Assignable Users API is working\n";
        echo "Users count: " . count($data['data']) . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}
echo "\n";

// 6. 测试直接分配功能
echo "6. Testing Direct Assign API...\n";
$result = testEndpoint($baseUrl . '/api/assignment/direct-assign', 'POST', [
    'process_id' => 1,
    'assigned_to' => 2,
    'reviewer' => 3
]);
echo "HTTP Code: " . $result['http_code'] . "\n";
if ($result['response']) {
    $data = json_decode($result['response'], true);
    if ($data && $data['success']) {
        echo "✓ Direct Assign API is working\n";
        echo "Message: " . $data['message'] . "\n";
    } else {
        echo "✗ API returned error: " . $result['response'] . "\n";
    }
} else {
    echo "✗ Failed to get response\n";
}

echo "\nAssignment API tests completed.\n";
