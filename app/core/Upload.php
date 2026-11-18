<?php
// Saving, finding and deleting request photos in storage/uploads.
// That folder is outside public/, so photos are only shown through the
// /requests/photo route, which checks who is allowed to see them.

class Upload
{
    public const MAX_BYTES = 2 * 1024 * 1024;   // 2 MB

    // Allowed image types and the file extension saved for each.
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    /**
     * Save an uploaded photo from a form field (e.g. $_FILES['photo']).
     * Returns [saved file name or null, error message or null].
     * When no file was chosen, both are null.
     */
    public static function savePhoto(?array $file): array
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return [null, null];
        }
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || $file['size'] > self::MAX_BYTES) {
            return [null, 'The photo must be 2 MB or smaller.'];
        }
        if ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return [null, 'The photo could not be uploaded. Please try again.'];
        }

        // Check the real file contents, not the name the user gave it.
        $info = @getimagesize($file['tmp_name']);
        if ($info === false || !isset(self::TYPES[$info['mime']])) {
            return [null, 'The photo must be a JPG or PNG image.'];
        }

        // Random name, so files cannot be guessed or overwrite each other.
        $name = bin2hex(random_bytes(16)) . '.' . self::TYPES[$info['mime']];

        if (!move_uploaded_file($file['tmp_name'], self::directory() . '/' . $name)) {
            return [null, 'The photo could not be saved. Please try again.'];
        }

        return [$name, null];
    }

    /** Full path of a saved photo, or null if the name is invalid or the file is missing. */
    public static function path(?string $name): ?string
    {
        if (!self::isValidName($name)) {
            return null;
        }
        $path = self::directory() . '/' . $name;

        return is_file($path) ? $path : null;
    }

    /** Delete a saved photo. Does nothing if there is none. */
    public static function delete(?string $name): void
    {
        $path = self::path($name);
        if ($path !== null) {
            unlink($path);
        }
    }

    /** MIME type to send to the browser for a saved photo. */
    public static function mimeType(string $name): string
    {
        return str_ends_with($name, '.png') ? 'image/png' : 'image/jpeg';
    }

    private static function directory(): string
    {
        $dir = ROOT_PATH . '/storage/uploads';

        // Some upload tools skip empty folders; create it if it did not make it to the server.
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /** Only names created by savePhoto() are accepted, so no other file can be reached. */
    private static function isValidName(?string $name): bool
    {
        return $name !== null && preg_match('/^[a-f0-9]{32}\.(jpg|png)$/', $name) === 1;
    }
}
