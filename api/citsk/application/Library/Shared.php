<?php

namespace Citsk\Library;

use Exception;

final class Shared
{

    /**
     * @param bool $includeBraces
     *
     * @return string
     */
    public static function getGUID(bool $includeBraces = false): string
    {
        if (function_exists('com_create_guid')) {
            if ($includeBraces === true) {
                return com_create_guid();
            } else {
                return substr(com_create_guid(), 1, 36);
            }
        } else {
            mt_srand((float) microtime() * 10000);
            $charid = md5(uniqid(rand(), true));

            $guid = substr($charid, 0, 8) . '-' .
            substr($charid, 8, 4) . '-' .
            substr($charid, 12, 4) . '-' .
            substr($charid, 16, 4) . '-' .
            substr($charid, 20, 12);

            if ($includeBraces) {
                $guid = '{' . $guid . '}';
            }

            return $guid;
        }
    }

    /**
     * @param mixed string
     *
     * @return string
     */
    public static function currentTimestamp(string $dateFormat = 'Y-m-d H:i:s'): string
    {

        return date($dateFormat);
    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function getRandomPasswordHash(int $length = 12): string
    {

        $result      = [];
        $alphabet    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890!@#$%^&*()';
        $alphaLength = strlen($alphabet) - 1;

        for ($i = 0; $i < $length; $i++) {
            $n        = rand(0, $alphaLength);
            $result[] = $alphabet[$n];
        }

        return implode($result);
    }

    /**
     * @return string
     */
    public static function getIpAdress(): string
    {
        $ip = null;

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * @param string $stroke
     *
     * @return string
     */
    public static function toCamelCase(string $stroke): string
    {

        if (!strpos($stroke, '-') && !strpos($stroke, '_') && !strpos($stroke, ".")) {
            return $stroke;
        }

        $result   = null;
        $tmpArray = [];

        if (strpos($stroke, '-')) {
            $tmpArray = explode('-', $stroke);
        }

        if (strpos($stroke, '_')) {
            $tmpArray = explode('_', $stroke);
        }

        if (strpos($stroke, '.')) {
            $tmpArray = explode('.', $stroke);
        }

        for ($i = 0; $i < count($tmpArray); $i++) {
            $result .= ($i == 0) ? $tmpArray[$i] : ucfirst($tmpArray[$i]);

        }

        return $result;
    }

    /**
     * @param string $stroke
     *
     * @return string
     */
    public static function toSnakeCase(string $stroke): string
    {

        return mb_strtolower(preg_replace("/((?<=[^$])[A-Z]|(?<!\d|^)\d)/", '_$1', $stroke));

    }

    /**
     * @param int $length
     *
     * @return string
     */
    public static function getHashString(int $length = 32): string
    {

        $length = ($length < 4) ? 4 : $length;

        return bin2hex(random_bytes(($length - ($length % 2)) / 2));
    }

    /**
     * @param string $directory
     *
     * @return void
     */
    public static function removeDirectory(string $directory): void
    {

        if (is_dir($directory)) {

            array_walk(glob("$directory/*"), function ($file) {
                unlink($file);
            });

            rmdir($directory);
        }
    }

    /**
     * @return array
     */
    public static function uploadFiles(): array
    {

        $uploadsDirHashName = self::getHashString(10);
        $uploadsDirFullPath = $_SERVER['DOCUMENT_ROOT'] . "/uploads/$uploadsDirHashName";
        $isFileUploaded     = false;
        $fileMeta           = [];

        mkdir($uploadsDirFullPath, 0777, true);

        foreach ($_FILES as $file) {

            if (!in_array($file['type'], ALLOWED_MIME)) {
                self::removeDirectory($uploadsDirFullPath);

                continue;
            }

            foreach (BLACKLIST as $item) {
                if (preg_match("/$item\$/i", $file['name'])) {
                    self::removeDirectory($uploadsDirFullPath);

                    continue;
                }
            }

            if ($file['size'] <= 10485760) {
                $fileHashName   = self::getHashString();
                $tmpName        = $file['tmp_name'];
                $pathInfo       = pathinfo($file['name']);
                $fileName       = "$fileHashName.{$pathInfo['extension']}";
                $isFileUploaded = move_uploaded_file($tmpName, "$uploadsDirFullPath/$fileName");

                if ($isFileUploaded) {
                    $fileMeta[] = [
                        'file_name'     => $fileName,
                        'sub_dir'       => $uploadsDirHashName,
                        'original_name' => $file['name'],
                        'size'          => $file['size'],
                        'type'          => $file['type'],
                    ];
                } else {
                    throw new Exception("File not uploaded");
                }
            }
        }

        return $fileMeta;
    }

    /**
     * @param string|null $dataType
     * @param string|null $value
     *
     * @return mixed
     */
    public static function getDataType(string $dataType = null, ?string $value = null)
    {

        if ($dataType == 'bool') {
            return $value == '1' ? true : false;
        }

        if ($dataType == 'number') {
            return intval($value);
        }

        if ($dataType == 'string') {
            return $value;
        }

        return $value;
    }

    /**
     * @param string $latitude
     * @param string $longitude
     *
     * @return array
     */
    public static function getCoords(string $latitude, string $longitude): array
    {
        return [(double) $latitude, (double) $longitude];
    }

    /**
     * @param mixed $rows
     *
     * @return string|null
     */
    public static function joinCategories($rows): ?string
    {
        $result = null;

        if (!$rows) {
            return $result;
        }

        array_walk($rows, function (&$row) use (&$result) {
            $result .= "{$row['label']}, ";

        });

        return trim($result, ", ");
    }

    /**
     * @param int $value
     *
     * @return string
     */
    public static function getPublishStateStroke(int $value): string
    {
        if ($value) {
            return 'черновик';
        } else {
            return 'опубликовано';
        }
    }

}
