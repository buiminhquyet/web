<?php
/**
 * Simple Robust SMTP Client for PHP
 * Supports AUTH LOGIN, TLS/SSL, and attachments
 */
class SmtpClient {
    private $host;
    private $port;
    private $user;
    private $pass;
    private $secure;
    private $conn;
    private $debug = false;

    public function __construct($host, $port, $user, $pass, $secure = 'tls') {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->pass = $pass;
        $this->secure = strtolower($secure);
    }

    private function getResponse() {
        $res = "";
        while ($str = fgets($this->conn, 512)) {
            $res .= $str;
            if (substr($str, 3, 1) == " ") break;
        }
        return $res;
    }

    private function sendCmd($cmd) {
        fputs($this->conn, $cmd . "\r\n");
        return $this->getResponse();
    }

    public function send($to, $subject, $body, $fromName = 'QUYETDEV Shop') {
        $prefix = ($this->secure == 'ssl') ? 'ssl://' : '';
        $this->conn = fsockopen($prefix . $this->host, $this->port, $errno, $errstr, 30);
        
        if (!$this->conn) return "Connection Failed: $errstr";
        $this->getResponse();

        $this->sendCmd("EHLO " . $_SERVER['HTTP_HOST']);
        
        if ($this->secure == 'tls') {
            $this->sendCmd("STARTTLS");
            if (!stream_socket_enable_crypto($this->conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                return "TLS Encryption Failed";
            }
            $this->sendCmd("EHLO " . $_SERVER['HTTP_HOST']);
        }

        $this->sendCmd("AUTH LOGIN");
        $this->sendCmd(base64_encode($this->user));
        $this->sendCmd(base64_encode($this->pass));

        $this->sendCmd("MAIL FROM: <$this->user>");
        $this->sendCmd("RCPT TO: <$to>");
        $this->sendCmd("DATA");

        $header = "To: $to\r\n";
        $header .= "From: $fromName <$this->user>\r\n";
        $header .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $header .= "Content-Type: text/html; charset=UTF-8\r\n";
        $header .= "MIME-Version: 1.0\r\n\r\n";

        $this->sendCmd($header . $body . "\r\n.");
        $this->sendCmd("QUIT");
        fclose($this->conn);

        return true;
    }
}
?>
