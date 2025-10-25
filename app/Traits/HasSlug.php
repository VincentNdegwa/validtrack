<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasSlug
{
    /**
     * Get the encrypted ID (slug) for the model.
     */
    public function getSlugAttribute(): string
    {
        return Crypt::encrypt($this->id);
    }

    /**
     * Find a model by its encrypted slug or fail.
     *
     * @return static
     */
    public static function findBySlugOrFail(string $slug)
    {
        try {
            $id = Crypt::decrypt($slug);

            return static::findOrFail($id);
        } catch (\Exception $e) {
            abort(404);
        }
    }
}
