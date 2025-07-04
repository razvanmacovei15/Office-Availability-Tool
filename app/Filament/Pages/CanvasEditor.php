<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CanvasEditor extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil';
    protected static string $view = 'filament.pages.canvas-editor';

    protected static ?string $navigationLabel = 'Canvas Editor';
    protected static ?string $slug = 'canvas-editor';
}
