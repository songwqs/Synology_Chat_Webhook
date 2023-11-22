<?php
//http://yourdomain.com/?token=shFE8x3XfhPdHJcN&userCode=user123&messageText=信息
// Synology Chat Webhook URL
$webhookURL = "https://yourQC.com/webapi/entry.cgi?api=SYNO.Chat.External&method=incoming&version=2&token=token_key"
// 安全标记  自定
$securityToken = "h3cxk6dh9kzmguy3q8vbsdy8kwt43hnk";
// 文件路径，用于记录 IP 地址的尝试次数
$logFilePath = __DIR__ . "/ip_attempts.log";

// 获取客户端 IP 地址
$clientIP = $_SERVER['REMOTE_ADDR'];

// 检查安全标记是否匹配
if ($_GET['token'] !== $securityToken) {
    echo "安全标记不匹配，拒绝访问\n";
    exit;
}

// 检查 IP 地址的尝试次数
$attempts = getAttemptsCount($clientIP);

// 设置允许的最大尝试次数
$maxAttempts = 5;

if ($attempts >= $maxAttempts) {
    echo "尝试次数过多，暂时拒绝服务\n";
    exit;
}

// 获取解码后的用户码和消息内容
$userCode = urldecode($_GET['userCode']) ?? "default"; // 默认为 "default"，如果未提供用户码参数
$messageText = urldecode($_GET['messageText']) ?? "Hello from Synology Chat PHP!"; // 默认消息内容为 "Hello from Synology Chat!"，如果未提供消息内容参数

// 构建 POST 数据
$postData = [
    "payload" => json_encode([
        "text" => "[$userCode] $messageText"
    ])
];

// 设置请求选项
$options = [
    "http" => [
        "header" => "Content-type: application/json",
        "method" => "POST",
        "content" => http_build_query($postData),
    ],
];

// 创建上下文流
$context = stream_context_create($options);

// 发送请求
$result = file_get_contents($webhookURL, false, $context);

// 检查响应是否成功
if ($result !== false) {
    echo "Synology Chat推送成功\n";
} else {
    echo "Synology Chat推送失败，请检查Webhook URL是否填写正确\n";
}

// 获取 IP 地址的尝试次数
function getAttemptsCount($ip)
{
    global $logFilePath;

    if (file_exists($logFilePath)) {
        $attemptsData = json_decode(file_get_contents($logFilePath), true);

        return $attemptsData[$ip] ?? 0;
    }

    return 0;
}
