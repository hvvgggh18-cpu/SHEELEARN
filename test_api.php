<?php

// Test the API response directly
$url = 'http://127.0.0.1:8000/api/auth/register';

$data = [
    'name' => 'Test User',
    'email' => 'test_' . time() . '@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\nAccept: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ]
];

$context = stream_context_create($options);
$result = @file_get_contents($url, false, $context);

echo "Response Status: " . (isset($http_response_header) ? $http_response_header[0] : 'Unknown') . "\n";
echo "Response Body:\n";
echo $result ? $result : 'No response';
echo "\n";
?>
