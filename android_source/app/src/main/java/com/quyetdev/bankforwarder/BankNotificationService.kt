package com.quyetdev.bankforwarder

import android.app.Notification
import android.app.NotificationChannel
import android.app.NotificationManager
import android.app.PendingIntent
import android.content.Context
import android.content.Intent
import android.os.Build
import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import android.util.Log
import androidx.core.app.NotificationCompat
import okhttp3.*
import java.io.IOException

class BankNotificationService : NotificationListenerService() {

    private lateinit var settings: SettingsManager
    private val client = OkHttpClient()
    private val CHANNEL_ID = "BankMonitorChannel"
    private val NOTIFICATION_ID = 99

    override fun onCreate() {
        super.onCreate()
        settings = SettingsManager(this)
        createNotificationChannel()
        startForeground(NOTIFICATION_ID, createForegroundNotification())
        sendStatusBroadcast("Dịch vụ TIỀN TUYẾN đã khởi động thành công.")
    }

    override fun onStartCommand(intent: Intent?, flags: Int, startId: Int): Int {
        // Đảm bảo service tự khởi động lại nếu lỡ bị hệ điều hành tắt
        return START_STICKY
    }

    private fun createNotificationChannel() {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Giám sát Ngân hàng",
                NotificationManager.IMPORTANCE_LOW
            ).apply {
                description = "Kênh này giúp App chạy ngầm vĩnh viễn để nhận thông báo nạp tiền."
            }
            val manager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager
            manager.createNotificationChannel(channel)
        }
    }

    private fun createForegroundNotification(): Notification {
        val pendingIntent: PendingIntent = Intent(this, MainActivity::class.java).let { notificationIntent ->
            PendingIntent.getActivity(this, 0, notificationIntent, PendingIntent.FLAG_IMMUTABLE)
        }

        return NotificationCompat.Builder(this, CHANNEL_ID)
            .setContentTitle("Bank Monitor is ACTIVE")
            .setContentText("Hệ thống đang chạy ngầm và sẵn sàng nhận bank 24/7.")
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentIntent(pendingIntent)
            .setOngoing(true)
            .build()
    }

    override fun onNotificationPosted(sbn: StatusBarNotification) {
        if (!settings.isServiceEnabled) return

        val packageName = sbn.packageName
        val extras = sbn.notification.extras
        
        val title = extras.getString("android.title") ?: ""
        val text = extras.getCharSequence("android.text")?.toString() ?: ""
        val subText = extras.getCharSequence("android.subText")?.toString() ?: ""
        val bigText = extras.getCharSequence("android.bigText")?.toString() ?: ""
        val summaryText = extras.getCharSequence("android.summaryText")?.toString() ?: ""
        
        // 2. Gộp tất cả lại thành một chuỗi văn bản khổng lồ để quét
        val fullContent = "App: $packageName | Title: $title | Text: $text | Sub: $subText | Big: $bigText | Sum: $summaryText".trim()
        
        // LUÔN LUÔN HIỆN LÊN NHẬT KÝ ĐỂ THEO DÕI (X-RAY MODE)
        if (fullContent.isNotEmpty()) {
            sendStatusBroadcast("THẤY: $fullContent")
        }
        
        // 3. Quy tắc gửi lên Server: Phải chứa từ khóa QUYETDEV
        val hasKeyword = fullContent.contains("QUYETDEV", ignoreCase = true)

        if (hasKeyword && fullContent.isNotEmpty()) {
            sendStatusBroadcast("==> KHỚP 'QUYETDEV'! Đang gửi lên Website...")
            sendToServer(title, fullContent)
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
                    sendStatusBroadcast("Gửi thành công! Website đã nhận tiền. ✅")
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
    }

    override fun onTaskRemoved(rootIntent: Intent?) {
        // Trick: Khi bạn vuốt app đi, chúng ta phát một broadcast để service tự hồi sinh
        val restartServiceIntent = Intent(applicationContext, this.javaClass)
        restartServiceIntent.setPackage(packageName)
        startService(restartServiceIntent)
        super.onTaskRemoved(rootIntent)
    }
}
