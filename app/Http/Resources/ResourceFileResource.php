<?php

namespace App\Http\Resources;

use App\Models\ResourceFile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ResourceFile */
class ResourceFileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'entry_key' => $this->entry_key,
            'name' => $this->name ?? '资源文件',
            'status' => $this->status ?? '可查看',
            'platform' => $this->platform ?? 'Windows',
            'language' => $this->language ?? '简体中文',
            'size' => $this->size ?? '未知大小',
            'code' => $this->code,
            'extract_code' => $this->extract_code,
            'uploaded_at' => $this->uploaded_at ?? '刚刚',
            'download_detail' => $this->download_detail,
            'download_url' => $this->download_url,
            'uploader' => [
                'id' => $this->uploader?->getKey() ?? $this->uploader_id,
                'name' => $this->uploader?->name
                    ?? $this->uploader_name
                    ?? '匿名上传者',
                'avatar' => $this->uploader?->avatar
                    ?? $this->uploader_avatar,
            ],
            'action_label' => $this->action_label ?? '查看',
        ];
    }
}
