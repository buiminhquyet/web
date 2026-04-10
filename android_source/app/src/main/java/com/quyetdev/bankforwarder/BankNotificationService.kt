package com.quyetdev.bankforwarder

import android.service.notification.NotificationListenerService
import android.service.notification.StatusBarNotification
import android.util.Log
import okhttp3.*
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.RequestBody.Companion.toRequestBody
import java.io.IOException

class BankNotificationService : NotificationListenerService() {

    private val client = OkHttpClient()
    private lateinit var settings: SettingsManager

    override fun onCreate() {
        super.onCreate()
        settings = SettingsManager(this)
    }

    override fun onNotificationPosted(sbn: StatusBarNotification) {
        if (!settings.isServiceEnabled) return

        val packageName = sbn.packageName
        val extras = sbn.notification.extras
        val title = extras.getString("android.title") ?: ""
        val text = extras.getCharSequence("android.text")?.toString() ?: ""

        // Lọc các app ngân hàng phổ biến
        val bankPackages = listOf(
            "com.mbmobile", // MB Bank
            "com.vietcombank.mgcb", // Vietcombank
            "com.vnpay.vcb", // VCB Digibank
            "vn.com.techcombank.bb.app", // Techcombank
            "com.zing.zalopay", // ZaloPay
            "vn.com.vpb.neo" // VPBank
        )

        if (bankPackages.contains(packageName) || text.contains("QUYETDEV", ignoreCase = true)) {
            sendToServer(title, text)
        }
    }

    private fun sendToServer(title: String, text: String) {
        val url = settings.apiUrl
        if (url.isEmpty()) return

        val formBody = FormBody.Builder()
            .add("token", settings.apiToken)
            .add("title", title)
            .add("text", text)
            .build()

        val request = Request.Builder()
            .url(url)
            .header("X-Private-Token", settings.apiToken)
            .post(formBody)
            .build()

        client.newCall(request).enqueue(object : Callback {
            override fun onFailure(call: Call, e: IOException) {
                Log.e("BankService", "Failed to send: ${e.message}")
            }

            override fun onResponse(call: Call, response: Response) {
                Log.d("BankService", "Response: ${response.code}")
                response.close()
            }
        })
    }
}
