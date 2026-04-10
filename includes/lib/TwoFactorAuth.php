<?php

/**
 * TwoFactorAuth - A simple TOTP library for Google Authenticator compatibility
 */
class TwoFactorAuth {
    private $_codeLength = 6;

    /**
     * Create new secret
     */
    public function createSecret($length = 16) {
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[rand(0, 31)];
        }
        return $secret;
    }

    /**
     * Calculate the code for a secret and time index
     */
    public function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretKey = $this->_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        // Hash it with SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);
        // Use last nibble of result as index/offset
        $offset = ord(substr($hmac, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hmac, $offset, 4);

        // Unpack binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 31 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR Code URL (using QRServer API for better reliability)
     */
    public function getQRCodeUrl($name, $secret, $title = null) {
        $url = "otpauth://totp/$name?secret=$secret";
        if ($title) $url .= "&issuer=" . urlencode($title);
        
        return "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($url);
    }

    /**
     * Verify if the code is correct
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $currentTimeSlice = null) {
        if ($currentTimeSlice === null) {
            $currentTimeSlice = floor(time() / 30);
        }

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode == $code) {
                return true;
            }
        }

        return false;
    }

    /**
     * Helper to decode base32
     */
    private function _base32Decode($secret) {
        if (empty($secret)) return '';

        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));

        $paddingCharCount = substr_count($secret, '=');
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;

        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat('=', $allowedValues[$i])) return false;
        }

        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = "";
            if (!in_array($secret[$i], str_split($base32chars))) return false;
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
            }
        }
        return $binaryString;
    }
}
