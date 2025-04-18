<?php

$botToken = "8114965260:AAG_F37r1b8fc5ZZ3TAhZAtcgpLutSefL6Y"; // Replace with your bot token
$groupId = "-1002321042237"; // Your Telegram group ID

$update = json_decode(file_get_contents("php://input"), TRUE);

if (isset($update["message"])) {
    $message = $update["message"];
    $chatId = $message["chat"]["id"];
    $messageId = $message["message_id"];
    $text = isset($message["text"]) ? trim($message["text"]) : "";
    $userId = $message["from"]["id"];
    $username = isset($message["from"]["username"]) ? "@".$message["from"]["username"] : "User";

    // Ensure bot works only inside the group
    if ($chatId != $groupId) {
        sendMessage($chatId, "🔔 Sirf hamare group me bot ka use karein!\n🔗 Join here: @thbots1", $messageId);
        exit;
    }

    // Check for "/like {region} {uid}" command
    if (preg_match('/^\/like\s+(\w+)\s+(\d+)$/', $text, $matches)) {
        $region = $matches[1];
        $uid = $matches[2];

        // Processing message
        $processingMsgId = sendMessage($chatId, "⏳ Processing your request...", $messageId);

        // API Call
        $apiUrl = "https://likesapi.thory.in/like?uid=$uid&region=$region&key=cute";
        $apiResponse = file_get_contents($apiUrl);
        $data = json_decode($apiResponse, true);

        // Check API Response
        if ($data && isset($data["status"])) {
            if ($data["status"] == 2) {
                $replyMessage = "❌ *Max Likes Reached!*\n\n⚠️ You cannot give more likes today.";
            } else if (isset($data["response"])) {
                $response = $data["response"];
                $replyMessage = "✅ *Likes Updated!*\n\n".
                    "👍 *Likes Given:* {$response['LikesGivenByAPI']}\n".
                    "📊 *Before:* {$response['LikesbeforeCommand']}\n".
                    "📈 *After:* {$response['LikesafterCommand']}\n".
                    "🎮 *Player Level:* {$response['PlayerLevel']}\n".
                    "👤 *Nickname:* {$response['PlayerNickname']}\n".
                    "🆔 *UID:* {$response['UID']}\n\n".
                    "🔰 *Requested by:* $username";
            } else {
                $replyMessage = "❌ *API Error! Please try again.*";
            }
        } else {
            $replyMessage = "❌ *Maxlikes for Today*";
        }

        // Edit processing message with final response
        editMessage($chatId, $processingMsgId, $replyMessage);
    }
}

// Function to send a message as a reply
function sendMessage($chatId, $message, $replyToMessageId = null) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown',
        'reply_to_message_id' => $replyToMessageId
    ];
    $result = json_decode(sendRequest($url, $postData), true);
    return $result['result']['message_id'] ?? null;
}

// Function to edit a message
function editMessage($chatId, $messageId, $newText) {
    global $botToken;
    $url = "https://api.telegram.org/bot$botToken/editMessageText";
    $postData = [
        'chat_id' => $chatId,
        'message_id' => $messageId,
        'text' => $newText,
        'parse_mode' => 'Markdown'
    ];
    sendRequest($url, $postData);
}

// Function to send HTTP request
function sendRequest($url, $postData) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

?>
