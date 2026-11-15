<?php
// Optimizacion de imagenes subidas y conversion automatica a WebP

class ImageOptimizer {

    public static function detectMimeType(string $filePath, string $fallback = 'application/octet-stream'): string {
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($filePath);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($filePath);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }
        return $fallback;
    }

    public static function getWebpDir(string $uploadDir): string {
        $webpDir = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . 'webp' . DIRECTORY_SEPARATOR;
        if (!is_dir($webpDir)) {
            @mkdir($webpDir, 0755, true);
        }
        return $webpDir;
    }

    // Guarda el archivo subido, deja estaticos los GIF y crea copia ligera en WebP
    public static function processUpload(string $tmpPath, string $destinationPath, string $mimeType, string $uploadDir): bool {
        if ($mimeType === 'image/gif' && function_exists('imagecreatefromgif')) {
            $img = @imagecreatefromgif($tmpPath);
            if ($img !== false) {
                $transparentIndex = imagecolortransparent($img);
                if ($transparentIndex >= 0) {
                    imagepalettecopy($img, $img);
                    imagefill($img, 0, 0, $transparentIndex);
                    imagecolortransparent($img, $transparentIndex);
                }
                $savedGif = @imagegif($img, $destinationPath);
                imagedestroy($img);
                if (!$savedGif && !@move_uploaded_file($tmpPath, $destinationPath)) {
                    return false;
                }
            } elseif (!@move_uploaded_file($tmpPath, $destinationPath)) {
                return false;
            }
        } else {
            if (!@move_uploaded_file($tmpPath, $destinationPath)) {
                return false;
            }
        }

        if ($mimeType !== 'application/pdf') {
            self::generateWebp($destinationPath, $uploadDir);
        }

        return true;
    }

    public static function generateWebp(string $sourcePath, string $uploadDir): ?string {
        if (!file_exists($sourcePath) || !extension_loaded('gd') || !function_exists('imagewebp')) {
            return null;
        }

        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $webpPath = $webpDir . $filename . '.webp';

        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
            return $webpPath;
        }

        $content = @file_get_contents($sourcePath);
        if ($content === false || $content === '') {
            return null;
        }

        $img = @imagecreatefromstring($content);
        if ($img === false) {
            return null;
        }

        @imagepalettetotruecolor($img);
        @imagealphablending($img, true);
        @imagesavealpha($img, true);

        $saved = @imagewebp($img, $webpPath, 85);
        @imagedestroy($img);

        return $saved ? $webpPath : null;
    }

    public static function getWebpPath(string $sourcePath, string $uploadDir): ?string {
        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $webpPath = $webpDir . $filename . '.webp';

        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
            return $webpPath;
        }

        return self::generateWebp($sourcePath, $uploadDir);
    }

    public static function deleteWebp(string $sourceFilename, string $uploadDir): void {
        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourceFilename, PATHINFO_FILENAME);
        $webpPath = $webpDir . $filename . '.webp';
        if (file_exists($webpPath)) {
            @unlink($webpPath);
        }
    }

    // Envia el archivo al navegador (original si es descarga directa, o WebP para vista en pagina)
    public static function streamFile(string $filePath, string $originalMime, string $downloadName, bool $forceDownload, bool $preferWebp, string $uploadDir): void {
        if (!file_exists($filePath)) {
            http_response_code(404);
            die("El archivo solicitado no existe o fue removido.");
        }

        $safeDownloadName = str_replace('"', '', $downloadName);

        if ($forceDownload) {
            header('Content-Type: ' . $originalMime);
            header('Content-Disposition: attachment; filename="' . $safeDownloadName . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

        $isPdf = ($originalMime === 'application/pdf' || str_contains($originalMime, 'pdf'));
        if ($preferWebp && !$isPdf) {
            $webpPath = self::getWebpPath($filePath, $uploadDir);
            if ($webpPath && file_exists($webpPath)) {
                header('Content-Type: image/webp');
                header('Content-Disposition: inline');
                header('Content-Length: ' . filesize($webpPath));
                header('Cache-Control: public, max-age=86400');
                readfile($webpPath);
                exit;
            }
        }

        header('Content-Type: ' . $originalMime);
        header('Content-Disposition: inline; filename="' . $safeDownloadName . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: public, max-age=86400');
        readfile($filePath);
        exit;
    }
}
