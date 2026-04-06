<?php

namespace App\Classes;

use Deifhelt\LaravelReports\Interfaces\PreviewWindowOpener;
use Native\Desktop\Facades\Window;

class NativePhpWindowOpener implements PreviewWindowOpener
{
    public function openPdfWindow(string $route, array $params, string $title): void
    {
        Window::open('pdf-preview-' . uniqid())
            ->route($route, $params)
            ->width(900)
            ->height(700)
            ->minWidth(600)
            ->minHeight(400)
            ->title($title)
            ->resizable(true)
            ->hideMenu()
            ->hideDevTools();
    }
}
