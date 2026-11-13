<?php

namespace App\Filament\Support;

use Filament\Forms\Components\RichEditor;

/** A plain visual editor for rich-text columns other than "body" (history, descriptions). */
class RichBody
{
    public static function field(string $name, string $label): RichEditor
    {
        return RichEditor::make($name)
            ->label($label)
            ->toolbarButtons([
                ['bold', 'italic', 'underline', 'link'],
                ['h2', 'h3', 'blockquote', 'bulletList', 'orderedList', 'horizontalRule'],
                ['undo', 'redo'],
            ])
            ->columnSpanFull();
    }
}
