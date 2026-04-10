        val packageName = sbn.packageName
        val extras = sbn.notification.extras
        
        val title = extras.getString("android.title") ?: ""
        val text = extras.getCharSequence("android.text")?.toString() ?: ""
        val subText = extras.getCharSequence("android.subText")?.toString() ?: ""
        val bigText = extras.getCharSequence("android.bigText")?.toString() ?: ""
        val summaryText = extras.getCharSequence("android.summaryText")?.toString() ?: ""
        val tickerText = sbn.notification.tickerText?.toString() ?: ""

        // DUMP TOÀN BỘ DỮ LIỆU ẨN (EXTRAS)
        var allExtras = ""
        try {
            for (key in extras.keySet()) {
                val value = extras.get(key)
                if (value != null) {
                    allExtras += "[$key: $value] "
                }
            }
        } catch (e: Exception) {}
        
        // 2. Gộp tất cả lại thành một chuỗi văn bản khổng lồ để quét
        val fullContent = "App: $packageName | Title: $title | Text: $text | Sub: $subText | Ticker: $tickerText | Extras: $allExtras".trim()
        
        // LUÔN LUÔN HIỆN LÊN NHẬT KÝ ĐỂ THEO DÕI (DEEP DUMP MODE)
        if (fullContent.isNotEmpty()) {
            sendStatusBroadcast("QUÉT TOÀN BỘ: $fullContent")
        }
        
        // 3. Quy tắc gửi lên Server: Phải chứa từ khóa QUYETDEV
        val hasKeyword = fullContent.contains("QUYETDEV", ignoreCase = true)

        if (hasKeyword) {
            sendStatusBroadcast("==> TÌM THẤY 'QUYETDEV' TRONG DỮ LIỆU! Đang gửi lên Website...")
            sendToServer(title, fullContent)
        }
