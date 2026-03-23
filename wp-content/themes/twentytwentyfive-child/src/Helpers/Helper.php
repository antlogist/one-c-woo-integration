<?php

namespace TwentytwentyfiveChild\Helpers;

class Helper
{
    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
