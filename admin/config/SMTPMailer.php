<?php

class SMTPMailer
{
    private $host;
    private $port;
    private $user;
    private $pass;
    private $secure;
    
    public $errorLog = [];

    public function __construct($host, $port, $user, $pass, $secure = 'ssl')
    {
        $this->host   = $host;
        $this->port   = $port;
        $this->user   = $user;
        $this->pass   = $pass;
        $this->secure = strtolower($secure);
    }

    public function send($to, $subject, $body, $headers = '')
    {
        $this->errorLog = [];
        
        // Auto-detect ssl/tls based on port to resolve UI setting mismatches
        $sec = $this->secure;
        if (intval($this->port) === 587) {
            $sec = 'tls';
        } elseif (intval($this->port) === 465) {
            $sec = 'ssl';
        }

        $server = $this->host;
        if ($sec === 'ssl') {
            $server = 'ssl://' . $this->host;
        }

        $this->errorLog[] = "Connecting to $server on port $this->port (Security: $sec)...";
        $socket = @fsockopen($server, $this->port, $errno, $errstr, 15);
        if (!$socket) {
            $this->errorLog[] = "Connection failed: $errstr ($errno)";
            return false;
        }

        if (!$this->readResponse($socket, '220')) {
            $this->errorLog[] = "Expected greeting (220) failed.";
            fclose($socket);
            return false;
        }

        if (!$this->writeCommand($socket, "EHLO localhost", '250')) {
            $this->errorLog[] = "EHLO greeting failed.";
            fclose($socket);
            return false;
        }

        if ($sec === 'tls') {
            if (!$this->writeCommand($socket, "STARTTLS", '220')) {
                $this->errorLog[] = "STARTTLS negotiation failed.";
                fclose($socket);
                return false;
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                $this->errorLog[] = "Failed to enable TLS encryption.";
                fclose($socket);
                return false;
            }
            // Re-negotiate EHLO after TLS start
            if (!$this->writeCommand($socket, "EHLO localhost", '250')) {
                $this->errorLog[] = "EHLO after TLS failed.";
                fclose($socket);
                return false;
            }
        }

        if (!empty($this->user) && !empty($this->pass)) {
            if (!$this->writeCommand($socket, "AUTH LOGIN", '334')) {
                $this->errorLog[] = "AUTH LOGIN command rejected.";
                fclose($socket);
                return false;
            }
            if (!$this->writeCommand($socket, base64_encode($this->user), '334')) {
                $this->errorLog[] = "Username rejected by server.";
                fclose($socket);
                return false;
            }
            if (!$this->writeCommand($socket, base64_encode($this->pass), '235')) {
                $this->errorLog[] = "Password/Authentication failed.";
                fclose($socket);
                return false;
            }
        }

        $fromMail = !empty($this->user) ? $this->user : 'noreply@accessride.com';
        if (!$this->writeCommand($socket, "MAIL FROM: <$fromMail>", '250')) {
            $this->errorLog[] = "MAIL FROM rejected.";
            fclose($socket);
            return false;
        }
        if (!$this->writeCommand($socket, "RCPT TO: <$to>", '250')) {
            $this->errorLog[] = "RCPT TO rejected.";
            fclose($socket);
            return false;
        }

        if (!$this->writeCommand($socket, "DATA", '354')) {
            $this->errorLog[] = "DATA command rejected.";
            fclose($socket);
            return false;
        }

        $dataHeaders = "To: $to\r\n";
        $dataHeaders .= "Subject: $subject\r\n";
        if (strpos($headers, 'Content-Type') === false) {
            $dataHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
        }
        if (!empty($headers)) {
            $dataHeaders .= trim($headers) . "\r\n";
        }
        $dataHeaders .= "\r\n";

        fwrite($socket, $dataHeaders . $body . "\r\n.\r\n");
        if (!$this->readResponse($socket, '250')) {
            $this->errorLog[] = "Failed to transmit message body.";
            fclose($socket);
            return false;
        }

        $this->writeCommand($socket, "QUIT", '221');
        fclose($socket);
        return true;
    }

    private function readResponse($socket, $expectedCode)
    {
        $response = "";
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) === " ") {
                break;
            }
        }
        $this->errorLog[] = "Server response: " . trim($response);
        return substr($response, 0, 3) === $expectedCode;
    }

    private function writeCommand($socket, $cmd, $expectedCode)
    {
        $this->errorLog[] = "Client command: " . (strpos($cmd, "AUTH") === 0 || strlen($cmd) > 20 && !strpos($cmd, " ") ? "[SECURE_DATA]" : $cmd);
        fwrite($socket, $cmd . "\r\n");
        return $this->readResponse($socket, $expectedCode);
    }
}
?>
