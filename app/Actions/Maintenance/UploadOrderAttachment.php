<?php

namespace App\Actions\Maintenance;

use App\Models\Attachment;
use App\Models\MaintenanceOrder;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UploadOrderAttachment
{
    public const MAX_SIZE_SETTING_KEY = 'attachments.max_size_kb';

    public const ALLOWED_MIMES_SETTING_KEY = 'attachments.allowed_mimes';

    public const DEFAULT_MAX_SIZE_KB = 10240;

    /**
     * @var list<string>
     */
    public const DEFAULT_ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'webp'];

    /**
     * @var list<string>
     */
    public const DEFAULT_ALLOWED_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ];

    public function handle(MaintenanceOrder $order, UploadedFile $file, User $actor): Attachment
    {
        $this->assertValidFile($file);

        return DB::transaction(function () use ($order, $file, $actor): Attachment {
            $path = $file->store('maintenance-orders/'.$order->id, 'local');

            return $order->attachments()->create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => (string) ($file->getMimeType() ?: $file->getClientMimeType()),
                'size' => $file->getSize() ?: 0,
                'uploaded_by' => $actor->id,
            ]);
        });
    }

    /**
     * RN-GEN-006 — validate real MIME, extension and configurable size.
     */
    public function assertValidFile(UploadedFile $file): void
    {
        $maxKb = (int) (Setting::query()->where('key', self::MAX_SIZE_SETTING_KEY)->value('value') ?? self::DEFAULT_MAX_SIZE_KB);
        $sizeKb = (int) ceil(($file->getSize() ?: 0) / 1024);

        if ($sizeKb > $maxKb) {
            throw ValidationException::withMessages([
                'file' => __('El archivo excede el tamaño máximo permitido (:max KB).', ['max' => $maxKb]),
            ]);
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowedExtensions = self::DEFAULT_ALLOWED_EXTENSIONS;

        if (! in_array($extension, $allowedExtensions, true)) {
            throw ValidationException::withMessages([
                'file' => __('Tipo de archivo no permitido.'),
            ]);
        }

        $realMime = (string) ($file->getMimeType() ?: '');
        $allowedMimes = $this->allowedMimes();

        if ($realMime === '' || ! in_array($realMime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'file' => __('El contenido MIME del archivo no es válido.'),
            ]);
        }
    }

    /**
     * @return list<string>
     */
    public function allowedMimes(): array
    {
        $raw = Setting::query()->where('key', self::ALLOWED_MIMES_SETTING_KEY)->value('value');

        if ($raw === null || $raw === '') {
            return self::DEFAULT_ALLOWED_MIMES;
        }

        $decoded = json_decode((string) $raw, true);

        if (! is_array($decoded) || $decoded === []) {
            return self::DEFAULT_ALLOWED_MIMES;
        }

        return array_values(array_map('strval', $decoded));
    }

    public function maxSizeKb(): int
    {
        return (int) (Setting::query()->where('key', self::MAX_SIZE_SETTING_KEY)->value('value') ?? self::DEFAULT_MAX_SIZE_KB);
    }
}
