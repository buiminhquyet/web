package com.quyetdev.bankforwarder

import android.content.Intent
import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import android.util.Log
import okhttp3.*
import java.io.IOException

class BankNotificationService : NotificationListenerService() {

    private lateinit var settings: SettingsManager
    private val client = OkHttpClient()

    override fun onCreate() {
        super.onCreate()
        settings = SettingsManager(this)
        sendStatusBroadcast("Dịch vụ bắt thông báo đã khởi động.")
    }

    override fun onNotificationPosted(sbn: StatusBarNotification) {
        if (!settings.isServiceEnabled) return

        val packageName = sbn.packageName
        val extras = sbn.notification.extras
        
        // 1. Thu thập mọi mảnh dữ liệu có thể có trong thông báo
        val title = extras.getString("android.title") ?: ""
        val text = extras.getCharSequence("android.text")?.toString() ?: ""
        val subText = extras.getCharSequence("android.subText")?.toString() ?: ""
        val bigText = extras.getCharSequence("android.bigText")?.toString() ?: ""
        val summaryText = extras.getCharSequence("android.summaryText")?.toString() ?: ""
        
        // 2. Gộp tất cả lại thành một chuỗi văn bản khổng lồ để quét
        val fullContent = "$title | $text | $subText | $bigText | $summaryText".trim()
        
        // 3. Quy tắc bắt: Có nội dung và chứa từ khóa QUYETDEV (Không quan tâm app nào)
        val hasKeyword = fullContent.contains("QUYETDEV", ignoreCase = true)

        if (hasKeyword && fullContent.isNotEmpty()) {
            sendStatusBroadcast("Phát hiện dữ liệu: $fullContent")
            sendToServer(title, fullContent)
        } else if (fullContent.isNotEmpty()) {
            // Log nhẹ để người dùng biết app vẫn đang sống và thấy thông báo
            Log.d("BankService", "Bỏ qua thông báo không liên quan: $packageName")
        }
    }

    private fun sendToServer(title: String, text: String) {
        val url = settings.apiUrl
        val token = settings.apiToken

        if (url.isEmpty()) {
            sendStatusBroadcast("Lỗi: Chưa cấu hình URL Webhook.")
            return
        }

        val body = FormBody.Builder()
            .add("token", token)
            .add("title", title)
            .add("content", text)
            .build()

        val request = Request.Builder()
            .url(url)
            .addHeader("X-Private-Token", token)
            .post(body)
            .build()

        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                sendStatusBroadcast("Gửi thất bại: ${e.message}")
            }

            override fun onResponse(call: Call, response: Response) {
                val code = response.code
                if (code == 200) {
                    sendStatusBroadcast("Đã gửi thành công về Website! ✅")
                } else {
                    sendStatusBroadcast("Lỗi Server ($code): ${response.message}")
                }
                response.close()
            }
        })
    }
    
    private fun sendStatusBroadcast(message: String) {
        val intent = Intent("com.quyetdev.LOG_UPDATE")
        intent.putExtra("message", message)
        sendBroadcast(intent)
        Log.d("BankService", message)
    }
}
