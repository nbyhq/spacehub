<?php

namespace Database\Seeders;

use App\Models\Space;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SpacePhotoSeeder extends Seeder
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp'];

    /** [warna awal, warna akhir] per tipe ruang */
    private const COLORS = [
        'coworking' => ['#4f46e5', '#a5b4fc'],
        'meeting_room' => ['#059669', '#6ee7b7'],
        'private_office' => ['#d97706', '#fcd34d'],
        'event_hall' => ['#e11d48', '#fda4af'],
    ];

    public function run(): void
    {
        $disk = Storage::disk('public');
        $disk->makeDirectory('spaces');

        $count = ['foto ruang' => 0, 'foto tipe' => 0, 'ilustrasi' => 0, 'dilewati' => 0];

        foreach (Space::all() as $space) {
            // Jangan timpa foto yang diunggah manual lewat panel admin
            if ($space->photo && ! str_starts_with($space->photo, 'spaces/seed-')) {
                $count['dilewati']++;

                continue;
            }

            $slug = Str::slug($space->name);

            if ($source = $this->findImage($slug)) {
                $mode = 'foto ruang';
            } elseif ($source = $this->findImage($space->type)) {
                $mode = 'foto tipe';
            } else {
                $mode = 'ilustrasi';
            }

            if ($source) {
                $path = 'spaces/seed-'.$slug.'.'.pathinfo($source, PATHINFO_EXTENSION);
                $disk->put($path, file_get_contents($source));
            } else {
                $path = 'spaces/seed-'.$slug.'.svg';
                $disk->put($path, $this->svg($space));
            }

            if ($space->photo && $space->photo !== $path) {
                $disk->delete($space->photo);
            }

            $space->update(['photo' => $path]);
            $count[$mode]++;
        }

        $this->command?->info('SpacePhotoSeeder: '.collect($count)
            ->map(fn ($n, $label) => "$label $n")->implode(', ').'.');
    }

    private function findImage(string $name): ?string
    {
        foreach (self::EXTENSIONS as $ext) {
            $path = database_path("seeders/images/{$name}.{$ext}");

            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private function svg(Space $space): string
    {
        [$from, $to] = self::COLORS[$space->type] ?? self::COLORS['coworking'];
        $name = htmlspecialchars($space->name, ENT_XML1);
        $location = htmlspecialchars($space->location, ENT_XML1);
        $label = htmlspecialchars(Space::TYPES[$space->type] ?? $space->type, ENT_XML1);
        $seed = crc32($space->name);

        $blocks = '';
        for ($i = 0; $i < 5; $i++) {
            $height = 110 + (($seed >> ($i * 3)) % 5) * 28;
            $opacity = 12 + ($i % 3) * 5;
            $blocks .= sprintf(
                '<rect x="%d" y="%d" width="170" height="%d" rx="20" fill="#fff" fill-opacity="0.%d"/>',
                110 + $i * 205,
                540 - $height,
                $height,
                $opacity
            );
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1280 800" width="1280" height="800" role="img" aria-label="{$name}">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="{$from}"/>
      <stop offset="1" stop-color="{$to}"/>
    </linearGradient>
  </defs>
  <rect width="1280" height="800" fill="url(#bg)"/>
  <circle cx="1120" cy="110" r="260" fill="#fff" fill-opacity="0.12"/>
  <circle cx="90" cy="700" r="220" fill="#fff" fill-opacity="0.10"/>
  {$blocks}
  <rect x="0" y="540" width="1280" height="260" fill="#0f172a" fill-opacity="0.28"/>
  <rect x="80" y="70" width="{$this->chipWidth($label)}" height="48" rx="24" fill="#fff" fill-opacity="0.9"/>
  <text x="104" y="102" font-family="Inter, Arial, sans-serif" font-size="24" font-weight="700" fill="#1e1b4b">{$label}</text>
  <text x="80" y="660" font-family="Inter, Arial, sans-serif" font-size="60" font-weight="700" fill="#fff">{$name}</text>
  <text x="80" y="720" font-family="Inter, Arial, sans-serif" font-size="30" fill="#fff" fill-opacity="0.9">{$location}</text>
</svg>
SVG;
    }

    private function chipWidth(string $label): int
    {
        return 48 + (int) (mb_strlen($label) * 14.5);
    }
}
