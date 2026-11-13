<?php

namespace App\Filament\Support;

/** For create/edit pages using BodyEditor: in HTML mode, the HTML box's value becomes the body. */
trait HandlesRawBody
{
    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->applyRawBody($data);
    }

    /** @param  array<string, mixed>  $data */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->applyRawBody($data);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyRawBody(array $data): array
    {
        if (array_key_exists('body_raw', $data)) {
            $data['body'] = (string) $data['body_raw'];
            unset($data['body_raw']);
        }

        return $data;
    }
}
