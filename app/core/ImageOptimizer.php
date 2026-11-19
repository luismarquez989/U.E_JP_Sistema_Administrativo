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
            self::generateWebp($destinationPath, $uploadDir, 1400, '', 78);
            self::generateWebp($destinationPath, $uploadDir, 320, '_thumb', 75);
        }

        return true;
    }

    public static function generateWebp(string $sourcePath, string $uploadDir, int $maxDim = 1400, string $suffix = '', int $quality = 78): ?string {
        if (!file_exists($sourcePath) || !extension_loaded('gd') || !function_exists('imagewebp')) {
            return null;
        }

        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $webpPath = $webpDir . $filename . $suffix . '.webp';

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

        $origW = imagesx($img);
        $origH = imagesy($img);

        if ($maxDim > 0 && ($origW > $maxDim || $origH > $maxDim)) {
            $ratio = min($maxDim / $origW, $maxDim / $origH);
            $newW = max(1, (int)round($origW * $ratio));
            $newH = max(1, (int)round($origH * $ratio));
            $resized = imagecreatetruecolor($newW, $newH);
            if ($resized !== false) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $newW, $newH, $transparent);
                imagecopyresampled($resized, $img, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($img);
                $img = $resized;
            }
        } else {
            @imagepalettetotruecolor($img);
            @imagealphablending($img, true);
            @imagesavealpha($img, true);
        }

        $saved = @imagewebp($img, $webpPath, $quality);
        @imagedestroy($img);

        return $saved ? $webpPath : null;
    }

    public static function getWebpPath(string $sourcePath, string $uploadDir, string $size = ''): ?string {
        $isThumb = ($size === 'thumb');
        $suffix = $isThumb ? '_thumb' : '';
        $maxDim = $isThumb ? 320 : 1400;
        $quality = $isThumb ? 75 : 78;

        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $webpPath = $webpDir . $filename . $suffix . '.webp';

        if (file_exists($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
            return $webpPath;
        }

        return self::generateWebp($sourcePath, $uploadDir, $maxDim, $suffix, $quality);
    }

    public static function deleteWebp(string $sourceFilename, string $uploadDir): void {
        $webpDir = self::getWebpDir($uploadDir);
        $filename = pathinfo($sourceFilename, PATHINFO_FILENAME);
        foreach (['', '_thumb'] as $suffix) {
            $webpPath = $webpDir . $filename . $suffix . '.webp';
            if (file_exists($webpPath)) {
                @unlink($webpPath);
            }
        }
    }

    // Envia el archivo al navegador con cache real, ETag y miniatura ligera WebP
    public static function streamFile(string $filePath, string $originalMime, string $downloadName, bool $forceDownload, bool $preferWebp, string $uploadDir, string $size = ''): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (!file_exists($filePath)) {
            if (class_exists('Controller')) {
                Controller::renderError(404, 'Archivo no Encontrado', 'El archivo solicitado no existe o fue removido del servidor.');
            }
            http_response_code(404);
            require __DIR__ . '/../views/layouts/404.php';
            exit;
        }

        $safeDownloadName = str_replace('"', '', $downloadName);

        if ($forceDownload) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            header('Content-Type: ' . $originalMime);
            header('Content-Disposition: attachment; filename="' . $safeDownloadName . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

        $targetPath = $filePath;
        $targetMime = $originalMime;
        $isPdf = ($originalMime === 'application/pdf' || str_contains($originalMime, 'pdf'));

        if ($preferWebp && !$isPdf) {
            $webpPath = self::getWebpPath($filePath, $uploadDir, $size);
            if ($webpPath && file_exists($webpPath)) {
                $targetPath = $webpPath;
                $targetMime = 'image/webp';
            }
        }

        $mtime = @filemtime($targetPath) ?: time();
        $fsize = @filesize($targetPath) ?: 0;
        $etag = '"' . md5($targetPath . '|' . $mtime . '|' . $fsize) . '"';
        $lastModified = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header_remove('Pragma');
        header('Cache-Control: private, max-age=2592000');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 2592000) . ' GMT');
        header('Last-Modified: ' . $lastModified);
        header('ETag: ' . $etag);

        $ifNoneMatch = trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
        $ifModifiedSince = trim((string)($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? ''));
        if ($ifNoneMatch === $etag || ($ifModifiedSince !== '' && strtotime($ifModifiedSince) >= $mtime)) {
            http_response_code(304);
            exit;
        }

        header('Content-Type: ' . $targetMime);
        header('Content-Disposition: inline' . ($targetMime === 'image/webp' ? '' : '; filename="' . $safeDownloadName . '"'));
        header('Content-Length: ' . $fsize);
        readfile($targetPath);
        exit;
    }
}
