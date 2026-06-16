<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Site;
use App\Models\Subscription;
use Carbon\Carbon;
use Generator;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportNotionData extends Command
{
    protected $signature = 'notion:import
        {clients : Ruta al CSV "Clients ..._all.csv"}
        {pagos : Ruta al CSV "Pagos ..._all.csv"}
        {--fresh : Vacía clients/sites/subscriptions antes de importar}';

    protected $description = 'Importa Clientes, Sitios y Suscripciones desde los exports CSV de Notion';

    /**
     * Filas de "Clients" cuyo nombre de cliente real es otro (ver
     * CONTEXTO-MIGRACION-CRM.md, caso 2: Nicola Farioli / Pavel Palao).
     */
    private array $clientNameOverrides = [
        'Pavel Palao' => 'Nicola Farioli',
    ];

    private array $stats = ['clients' => 0, 'sites' => 0, 'subscriptions' => 0];

    /** @var array<int, string> */
    private array $warnings = [];

    public function handle(): int
    {
        $clientsPath = $this->argument('clients');
        $pagosPath = $this->argument('pagos');

        if (! is_file($clientsPath) || ! is_file($pagosPath)) {
            $this->error('No se encontró alguno de los CSV indicados.');

            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            Subscription::query()->delete();
            Site::query()->delete();
            Client::query()->delete();
            $this->warn('Tablas clients/sites/subscriptions vaciadas (--fresh).');
        }

        $this->importClients($clientsPath);

        // Asegura que "Pavel Palao" exista como cliente propio (vacío),
        // según la aclaración del usuario.
        $pavel = Client::firstOrCreate(
            ['name' => 'Pavel Palao'],
            ['email' => $this->placeholderEmail('Pavel Palao')]
        );
        if ($pavel->wasRecentlyCreated) {
            $this->stats['clients']++;
        }

        $this->importPagos($pagosPath);

        $this->report();

        return self::SUCCESS;
    }

    private function importClients(string $path): void
    {
        foreach ($this->readCsv($path) as $row) {
            $name = $this->cleanName($row['Cliente'] ?? '');
            if ($name === '') {
                continue;
            }

            $name = $this->clientNameOverrides[$name] ?? $name;

            $client = Client::firstOrCreate(
                ['name' => $name],
                ['email' => $this->placeholderEmail($name)]
            );
            if ($client->wasRecentlyCreated) {
                $this->stats['clients']++;
            }

            foreach ($this->buildSiteDrafts($row) as $draft) {
                $site = Site::firstOrCreate(
                    ['client_id' => $client->id, 'domain' => $draft['domain']],
                    $draft
                );
                if ($site->wasRecentlyCreated) {
                    $this->stats['sites']++;
                }
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildSiteDrafts(array $row): array
    {
        $domainsRaw = trim($row['Dominios / URL'] ?? '');
        if ($domainsRaw === '') {
            $domainsRaw = $row['Dominios'] ?? '';
        }

        $uniqueDomains = [];
        foreach ($this->splitLines($domainsRaw) as $line) {
            $normalized = $this->normalizeDomain($line);
            if ($normalized !== null && ! in_array($normalized, $uniqueDomains, true)) {
                $uniqueDomains[] = $normalized;
            }
        }

        if ($uniqueDomains === []) {
            return [];
        }

        $siteNames = $this->splitCommaList($row['SitoWeb'] ?? '');
        $adminUrls = $this->splitLines($row['URL Acceso'] ?? '');
        $usernames = $this->splitLines($row['Username'] ?? '');
        $passwords = $this->splitLines($row['Password'] ?? '');

        $drafts = [];

        foreach ($uniqueDomains as $i => $domain) {
            $adminUrlRaw = trim($adminUrls[$i] ?? $adminUrls[0] ?? '');
            $isUrl = $this->looksLikeUrl($adminUrlRaw);

            $drafts[] = [
                'name' => $siteNames[$i] ?? $siteNames[0] ?? $domain,
                'domain' => $domain,
                'admin_url' => $isUrl ? $adminUrlRaw : null,
                'cms_username' => $this->cleanUsername($usernames[$i] ?? $usernames[0] ?? null),
                'cms_password' => $this->blankToNull($passwords[$i] ?? $passwords[0] ?? null),
                'cms_type' => ($isUrl && preg_match('/wp-admin|wp-login|masking/i', $adminUrlRaw)) ? 'WordPress' : null,
                'hosting_provider' => (! $isUrl && $adminUrlRaw !== '') ? $adminUrlRaw : null,
                'has_blog' => false,
            ];
        }

        return $drafts;
    }

    private function importPagos(string $path): void
    {
        foreach ($this->readCsv($path) as $row) {
            $domainRaw = $this->splitLines($row['Dominio/URL'] ?? '')[0] ?? '';
            $domain = $this->normalizeDomain($domainRaw);

            $rawName = $this->cleanName($row['Client'] ?? '');
            if ($rawName === '' && $domain === null) {
                continue; // fila final vacía del export
            }

            $cleanedName = $this->cleanName(preg_replace('/\d+$/', '', $rawName) ?? '');
            $cleanedName = $this->clientNameOverrides[$cleanedName] ?? $cleanedName;

            $site = $domain !== null ? Site::where('domain', $domain)->first() : null;

            if (! $site) {
                $site = $this->createSiteFromPagosRow($row, $cleanedName, $domain);
            } else {
                $client = $site->client;
                if ($cleanedName !== '' && $cleanedName !== $client->name) {
                    $this->warnings[] = "Fila de Pagos a nombre de \"{$cleanedName}\" pero el dominio {$domain} pertenece a \"{$client->name}\" (sitio \"{$site->name}\").";
                }
            }

            $this->fillHostingProvider($site, $row);
            $this->createSubscription($site, $row);
        }
    }

    private function createSiteFromPagosRow(array $row, string $cleanedName, ?string $domain): Site
    {
        $clientName = $cleanedName !== ''
            ? $cleanedName
            : ($this->cleanName($row['Empresa'] ?? '') ?: $this->cleanName($row['SitoWeb'] ?? '') ?: 'Sin nombre');

        $client = Client::firstOrCreate(
            ['name' => $clientName],
            ['email' => $this->placeholderEmail($clientName)]
        );
        if ($client->wasRecentlyCreated) {
            $this->stats['clients']++;
            $this->warnings[] = "Cliente nuevo creado solo desde Pagos: \"{$clientName}\" (revisar email y datos).";
        }

        $siteName = $this->cleanName($row['SitoWeb'] ?? '')
            ?: $this->cleanName($row['Empresa'] ?? '')
            ?: ($domain ?? $clientName);

        $adminUrlRaw = trim($this->splitLines($row['URL Acceso'] ?? '')[0] ?? '');
        $isUrl = $this->looksLikeUrl($adminUrlRaw);

        $site = Site::create([
            'client_id' => $client->id,
            'name' => $siteName,
            'domain' => $domain,
            'admin_url' => $isUrl ? $adminUrlRaw : null,
            'cms_username' => $this->cleanUsername($this->splitLines($row['Username'] ?? '')[0] ?? null),
            'cms_password' => $this->blankToNull($this->splitLines($row['Password'] ?? '')[0] ?? null),
            'cms_type' => ($isUrl && preg_match('/wp-admin|wp-login|masking/i', $adminUrlRaw)) ? 'WordPress' : null,
            'hosting_provider' => null,
            'has_blog' => false,
        ]);
        $this->stats['sites']++;
        $this->warnings[] = "Sitio nuevo creado solo desde Pagos: \"{$siteName}\" ({$domain}) para cliente \"{$clientName}\" (revisar credenciales/datos).";

        return $site;
    }

    private function fillHostingProvider(Site $site, array $row): void
    {
        if (filled($site->hosting_provider)) {
            return;
        }

        $hostingEn = $this->cleanName($row['Hosting-En'] ?? '');
        if ($hostingEn === '') {
            return;
        }

        $provider = trim(explode('/', $hostingEn)[0]);
        if ($provider !== '') {
            $site->update(['hosting_provider' => $provider]);
        }
    }

    private function createSubscription(Site $site, array $row): void
    {
        [$price, $priceOriginal] = $this->parsePrice($row['Price Total (Optional)'] ?? '', $row['(D)Price Dominio'] ?? '');

        $billing = strtolower(trim($row['(H) Billing'] ?? ''));
        $billingCycle = $billing === 'monthly' ? 'monthly' : 'yearly';

        $startDate = $this->parseDate($row['(H) Inizio'] ?? '') ?? $this->parseDate($row['(Domain) Inizio'] ?? '');
        $renewalDate = $this->parseDate($row['(H) Scade'] ?? '') ?? $this->parseDate($row['(Domain) Scade'] ?? '');

        $startDateNote = null;
        if (! $startDate) {
            $startDate = $renewalDate ?? Carbon::now();
            $startDateNote = 'Fecha de inicio no registrada en Notion (se usó una fecha por defecto).';
        }

        $notionStatus = trim($row['Status'] ?? '');
        $status = $this->computeStatus($notionStatus, $renewalDate);

        $notes = implode("\n", array_filter([
            trim($row['Notes'] ?? ''),
            trim($row['Notas DOMINIO'] ?? ''),
            $notionStatus !== '' ? "Status original en Notion: {$notionStatus}." : null,
            $priceOriginal !== null ? "Precio original en Notion: {$priceOriginal}." : null,
            $startDateNote,
        ], fn ($v) => $v !== null && $v !== ''));

        Subscription::create([
            'site_id' => $site->id,
            'service_type' => $this->cleanName($row['Services'] ?? '') ?: 'Hosting',
            'price' => $price,
            'billing_cycle' => $billingCycle,
            'start_date' => $startDate,
            'renewal_date' => $renewalDate,
            'status' => $status,
            'notes' => $notes !== '' ? $notes : null,
        ]);
        $this->stats['subscriptions']++;
    }

    private function computeStatus(string $notionStatus, ?Carbon $renewalDate): string
    {
        if (str_contains(Str::lower($notionStatus), 'fuera')) {
            return 'fuera_de_servicio';
        }

        if (! $renewalDate) {
            return 'pagado';
        }

        $today = Carbon::now();
        if ($renewalDate->lt($today)) {
            return 'vencido';
        }

        if ($renewalDate->lte($today->copy()->addDays(60))) {
            return 'por_vencer';
        }

        return 'pagado';
    }

    /**
     * @return array{0: float, 1: ?string}
     */
    private function parsePrice(string ...$candidates): array
    {
        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);
            if ($candidate === '') {
                continue;
            }

            if (preg_match('/(\d+(?:[.,]\d{1,2})?)/', $candidate, $m)) {
                return [(float) str_replace(',', '.', $m[1]), $candidate];
            }

            return [0.0, $candidate];
        }

        return [0.0, null];
    }

    private function parseDate(string $value): ?Carbon
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $value)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeDomain(?string $raw): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }

        $raw = preg_replace('#^https?://#i', '', $raw);
        $raw = preg_replace('#^www\.#i', '', $raw);
        $raw = Str::lower($raw);
        $raw = explode('/', $raw)[0];
        $raw = explode('?', $raw)[0];

        return $raw !== '' ? $raw : null;
    }

    private function looksLikeUrl(string $value): bool
    {
        return $value !== '' && (preg_match('#^https?://#i', $value) === 1 || str_contains($value, '.'));
    }

    /**
     * @return array<int, string>
     */
    private function splitLines(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];

        return array_values(array_filter(array_map('trim', $lines), fn ($v) => $v !== ''));
    }

    /**
     * @return array<int, string>
     */
    private function splitCommaList(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn ($v) => $v !== ''));
    }

    private function cleanUsername(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return preg_replace('/^mailto:/i', '', $value);
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function cleanName(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function placeholderEmail(string $name): string
    {
        return Str::slug($name).'@pendiente.dgzconsulting.com';
    }

    /**
     * @return Generator<int, array<string, string>>
     */
    private function readCsv(string $path): Generator
    {
        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);
        $header[0] = ltrim($header[0], "\xEF\xBB\xBF");
        $header = array_map('trim', $header);

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $assoc = [];
            foreach ($header as $i => $key) {
                $assoc[$key] = $row[$i] ?? '';
            }

            yield $assoc;
        }

        fclose($handle);
    }

    private function report(): void
    {
        $this->newLine();
        $this->info('Importación completada:');
        $this->line("  Clientes nuevos: {$this->stats['clients']}");
        $this->line("  Sitios nuevos: {$this->stats['sites']}");
        $this->line("  Suscripciones creadas: {$this->stats['subscriptions']}");

        if ($this->warnings !== []) {
            $this->newLine();
            $this->warn('Casos para revisar a mano:');
            foreach ($this->warnings as $warning) {
                $this->line("  - {$warning}");
            }
        }
    }
}
