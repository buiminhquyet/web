package com.quyetdev.bankforwarder

import android.content.Intent
import android.os.Bundle
import android.provider.Settings
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import com.quyetdev.bankforwarder.databinding.ActivityMainBinding

class MainActivity : AppCompatActivity() {

    private lateinit var binding: ActivityMainBinding
    private lateinit var settings: SettingsManager

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        settings = SettingsManager(this)

        // Load current settings
        binding.editUrl.setText(settings.apiUrl)
        binding.editToken.setText(settings.apiToken)
        binding.switchService.isChecked = settings.isServiceEnabled

        binding.btnSave.setOnClickListener {
            settings.apiUrl = binding.editUrl.text.toString()
            settings.apiToken = binding.editToken.text.toString()
            settings.isServiceEnabled = binding.switchService.isChecked
            Toast.makeText(this, "Đã lưu cài đặt!", Toast.LENGTH_SHORT).show()
        }

        binding.btnPermission.setOnClickListener {
            val intent = Intent("android.settings.ACTION_NOTIFICATION_LISTENER_SETTINGS")
            startActivity(intent)
        }
        
        checkPermission()
    }

    private fun checkPermission() {
        val listeners = Settings.Secure.getString(contentResolver, "enabled_notification_listeners")
        if (listeners == null || !listeners.contains(packageName)) {
            binding.txtPermissionStatus.text = "Trạng thái quyền: CHƯA CẤP"
            binding.txtPermissionStatus.setTextColor(getColor(android.R.color.holo_red_dark))
        } else {
            binding.txtPermissionStatus.text = "Trạng thái quyền: ĐÃ CẤP"
            binding.txtPermissionStatus.setTextColor(getColor(android.R.color.holo_green_dark))
        }
    }

    override fun onResume() {
        super.onResume()
        checkPermission()
    }
}
