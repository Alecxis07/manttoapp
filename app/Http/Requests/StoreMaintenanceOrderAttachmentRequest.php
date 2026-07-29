<?php

namespace App\Http\Requests;

use App\Actions\Maintenance\UploadOrderAttachment;
use App\Models\MaintenanceOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreMaintenanceOrderAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var MaintenanceOrder $order */
        $order = $this->route('maintenance_order');

        return $this->user()?->can('attach', $order) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $uploader = app(UploadOrderAttachment::class);

        return [
            'file' => [
                'required',
                File::types(UploadOrderAttachment::DEFAULT_ALLOWED_EXTENSIONS)
                    ->max($uploader->maxSizeKb()),
            ],
        ];
    }
}
