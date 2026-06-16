<?php

namespace App\Observers;

use App\Models\Media;
use Illuminate\Support\Facades\Auth;

class MediaObserver
{
    public function creating(Media $media): void
    {
        // En el portal cliente, asignar client_id automáticamente
        if (Auth::guard('client')->check()) {
            $client = Auth::guard('client')->user();
            $media->client_id = $client->id;

            // Organizar en carpeta por cliente en R2
            if (empty($media->directory)) {
                $media->directory = 'clients/' . $client->id;
            }
        }
    }
}
