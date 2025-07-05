<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasSlug
{
    /**
     * Get the encrypted ID (slug) for the model.
     *
     * @return string
     */
    public function getSlugAttribute(): string
    {
        return Crypt::encrypt($this->id);
    }

    /**
     * Find a model by its encrypted slug or fail.
     *
     * @param string $slug
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
