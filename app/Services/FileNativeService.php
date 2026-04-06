<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileNativeService
{
    public function __construct(
        protected string $disk = 'local'
    ) {
    }

    public function store(Model $model, UploadedFile $file): string
    {
        $folder = strtolower(class_basename($model)) . 's';
        return $file->store($folder, $this->disk);
    }

    public function delete(Model $model, string $attribute = 'image'): bool
    {
        $path = $model->{$attribute};
        if ($path && Storage::disk($this->disk)->exists($path)) {
            return Storage::disk($this->disk)->delete($path);
        }
        return false;
    }

    public function replace(Model $model, UploadedFile $newFile, string $attribute = 'image'): string
    {
        $this->delete($model, $attribute);
        return $this->store($model, $newFile);
    }
}