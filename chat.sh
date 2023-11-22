#!/bin/bash
# 设置Synology Chat Webhook URL
Synology_Chat_URL="https://yourQC.com/webapi/entry.cgi?api=SYNO.Chat.External&method=incoming&version=2&token=token_key"

# 要发送的消息内容
message_text="Hello from Synology Chat!"
# 使用curl发送消息到Synology Chat
res=$(timeout 20s curl -s -X POST \
     $Synology_Chat_URL \
     -H "Content-Type: application/json" \
     -d method=incoming \
     -d version=2 \
     -d token=${Synology_Chat_URL#*token=} \
     -d "payload={\"text\":\"$message_text\"}")

# 提取success字段的值
resSuccess=$(echo "$res" | grep -o '"success":\w*' | cut -d':' -f2)
# 检查是否推送成功
if [[ $resSuccess == "true" ]]; then
    echo "Synology_Chat推送成功"
else
    echo "Synology_Chat推送失败，请检查Synology_Chat_URL是否填写正确"
fi
