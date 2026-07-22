<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GestorImagenes
{
    public static function reglas(): array
    {
        return [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png',
            'max:2048',
            'dimensions:min_width=300,min_height=300,max_width=1200,max_height=1200',
        ];
    }

    public static function guardar(Request $solicitud, string $carpeta, ?string $imagenActual = null): ?string
    {
        if (! $solicitud->hasFile('imagen')) {
            return $imagenActual;
        }

        self::eliminar($imagenActual);

        $archivoImagen = $solicitud->file('imagen');
        $nombreArchivo = Str::uuid().'.'.$archivoImagen->extension();
        $rutaArchivo = $archivoImagen->storeAs($carpeta, $nombreArchivo, 'public');

        return '/storage/'.$rutaArchivo;
    }

    public static function eliminar(?string $urlImagen): void
    {
        if (! $urlImagen || ! str_starts_with($urlImagen, '/storage/')) {
            return;
        }

        $rutaArchivo = Str::after($urlImagen, '/storage/');
        Storage::disk('public')->delete($rutaArchivo);
    }
}
