<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Body field with two modes. The visual editor cannot represent some migrated markup
 * (photo galleries, YouTube embeds, FAQ blocks), so records that contain it open in raw
 * HTML mode and keep that markup intact. All HTML is purified on save by the model.
 */
class BodyEditor
{
    private const RAW_MARKERS = ['pc-gallery', 'pc-video', '<details', '<iframe', '<table'];

    /** @return array<int, Component|Field> */
    public static function make(): array
    {
        return [
            Toggle::make('edit_raw_html')
                ->label('Edit as HTML')
                ->helperText('On for content with photo galleries, videos or FAQ blocks, which the visual editor would remove.')
                ->live()
                ->dehydrated(false)
                ->afterStateHydrated(function (Toggle $component) {
                    $body = (string) $component->getRecord()?->getAttribute('body');
                    $component->state(collect(self::RAW_MARKERS)->contains(fn ($marker) => str_contains($body, $marker)));
                }),

            RichEditor::make('body')
                ->label('Content')
                ->visible(fn (Get $get) => ! $get('edit_raw_html'))
                ->toolbarButtons([
                    ['bold', 'italic', 'underline', 'link'],
                    ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'horizontalRule'],
                    ['attachFiles'],
                    ['undo', 'redo'],
                ])
                ->fileAttachmentsDisk('public')
                ->fileAttachmentsDirectory('content')
                ->fileAttachmentsVisibility('public')
                ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                ->fileAttachmentsMaxSize(5120)
                ->columnSpanFull(),

            // Separate state key: the visual editor converts "body" into its own JSON format, so the
            // HTML box reads the stored record directly and HandlesRawBody copies it back on save.
            Textarea::make('body_raw')
                ->label('Content (HTML)')
                ->visible(fn (Get $get) => (bool) $get('edit_raw_html'))
                ->afterStateHydrated(fn (Textarea $component) => $component->state((string) $component->getRecord()?->getAttribute('body')))
                ->rows(22)
                ->extraInputAttributes(['class' => 'font-mono text-sm'])
                ->helperText('Scripts, styles and embeds from sites other than YouTube/Vimeo are removed automatically when you save.')
                ->columnSpanFull(),
        ];
    }
}
