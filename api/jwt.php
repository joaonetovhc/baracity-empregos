<?php
function gerarJWT(array $payload): string {
    $chave = 'b4r4c1tyX4ve3sç3kr3t4';

    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $base64Header = base64UrlEncode($header);
    $base64Payload = base64UrlEncode(json_encode($payload));

    $assinatura = hash_hmac('sha256', "$base64Header.$base64Payload", $chave, true);
    $base64Assinatura = base64UrlEncode($assinatura);

    return "$base64Header.$base64Payload.$base64Assinatura";
}

function base64UrlEncode(string $str): string {
    return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
}


function verificarJWT(string $token) {
    $chave = 'b4r4c1tyX4ve3sç3kr3t4';

    $partes = explode('.', $token);
    if (count($partes) !== 3) return false;

    [$base64Header, $base64Payload, $base64Assinatura] = $partes;

    $assinaturaVerificada = base64UrlEncode(
        hash_hmac('sha256', "$base64Header.$base64Payload", $chave, true)
    );

    if (!hash_equals($base64Assinatura, $assinaturaVerificada)) return false;

    $payload = json_decode(base64_decode($base64Payload), true);

    return $payload;
}
