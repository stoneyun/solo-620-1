<?php
/**
 * 通用工具类
 */
class Utils
{
    /**
     * 输出JSON响应
     *
     * @param int $code 状态码 0成功 非0失败
     * @param string $message 提示信息
     * @param mixed $data 数据
     */
    public static function jsonResponse($code, $message, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array(
            'code'    => (int)$code,
            'message' => $message,
            'data'    => $data,
        ), JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * 成功响应
     */
    public static function success($message = '操作成功', $data = null)
    {
        self::jsonResponse(0, $message, $data);
    }

    /**
     * 失败响应
     */
    public static function error($message = '操作失败', $code = 1)
    {
        self::jsonResponse($code, $message);
    }

    /**
     * 获取请求参数（GET/POST）
     *
     * @param string $key 参数名
     * @param mixed $default 默认值
     * @return mixed
     */
    public static function input($key, $default = null)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }
        $input = file_get_contents('php://input');
        if ($input) {
            $json = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($json[$key])) {
                return $json[$key];
            }
        }
        return $default;
    }

    /**
     * HTML输出转义，防止XSS
     *
     * @param string $str
     * @return string
     */
    public static function e($str)
    {
        if ($str === null) {
            return '';
        }
        return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 生成唯一邀请码
     *
     * @param int $length 长度
     * @return string
     */
    public static function generateCode($length = null)
    {
        if ($length === null) {
            $length = CODE_LENGTH;
        }
        $chars = CODE_CHARS;
        $max = strlen($chars) - 1;
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[mt_rand(0, $max)];
        }
        return $code;
    }

    /**
     * 生成唯一邀请码（循环校验数据库唯一性）
     *
     * @param object $db Database实例
     * @param int $length 长度
     * @param int $maxAttempts 最大尝试次数
     * @return string|null
     */
    public static function generateUniqueCode($db, $length = null, $maxAttempts = 100)
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = self::generateCode($length);
            $row = $db->fetchOne(
                'SELECT id FROM `invitation_codes` WHERE `code` = :code LIMIT 1',
                array(':code' => $code)
            );
            if (!$row) {
                return $code;
            }
        }
        return null;
    }

    /**
     * 校验状态值合法性
     *
     * @param mixed $status
     * @return bool
     */
    public static function isValidStatus($status)
    {
        return in_array((int)$status, array(1, 2, 3), true);
    }

    /**
     * 校验日期时间格式 Y-m-d H:i:s
     *
     * @param string $datetime
     * @return bool
     */
    public static function isValidDatetime($datetime)
    {
        if (empty($datetime)) {
            return false;
        }
        $dt = DateTime::createFromFormat('Y-m-d H:i:s', $datetime);
        return $dt && $dt->format('Y-m-d H:i:s') === $datetime;
    }
}
