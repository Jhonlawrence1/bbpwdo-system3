<?php
class JWT {
    private $secret_key = 'bbpwdo_secret_key_2026';
    private $algorithm = 'HS256';
    
    public function encode($payload) {
        $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => $this->algorithm]));
        $payload = base64_encode(json_encode($payload));
        $signature = base64_encode(hash_hmac('sha256', "$header.$payload", $this->secret_key, true));
        return "$header.$payload.$signature";
    }
    
    public function decode($token) {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;
        
        $signature = base64_encode(hash_hmac('sha256', "{$parts[0]}.{$parts[1]}", $this->secret_key, true));
        if ($signature !== $parts[2]) return false;
        
        return json_decode(base64_decode($parts[1]), true);
    }
    
    public function createToken($user) {
        $payload = [
            'id' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + 86400
        ];
        return $this->encode($payload);
    }
    
    public function verifyToken($token) {
        $payload = $this->decode($token);
        if (!$payload || !isset($payload['exp'])) return false;
        if ($payload['exp'] < time()) return false;
        return $payload;
    }
}

$jwt = new JWT();
?>