<x-filament-widgets::widget>
    <x-filament::section heading="Mis Suscripciones" icon="heroicon-o-credit-card">
        @if($subscriptions->isEmpty())
            <p class="text-sm text-zinc-500 dark:text-zinc-400">No tienes suscripciones activas.</p>
        @else
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($subscriptions as $sub)
                    @php
                        $isOverdue = $sub->renewal_date?->isPast();
                        $isSoon = !$isOverdue && $sub->renewal_date?->diffInDays(now()) <= 30;
                    @endphp
                    <div class="flex items-center justify-between py-3 first:pt-0 last:pb-0">
                        <div>
                            <div class="text-sm font-medium text-zinc-900 dark:text-white">
                                {{ $sub->service_type }}
                            </div>
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $sub->site->name }} · {{ $sub->billing_cycle }}
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-zinc-900 dark:text-white">
                                €{{ number_format($sub->price, 2) }}
                            </div>
                            @if($sub->renewal_date)
                                <div class="text-xs {{ $isOverdue ? 'text-red-600 font-medium' : ($isSoon ? 'text-amber-600' : 'text-zinc-500 dark:text-zinc-400') }}">
                                    @if($isOverdue)
                                        Vencido {{ $sub->renewal_date->format('d/m/Y') }}
                                    @else
                                        Renueva {{ $sub->renewal_date->format('d/m/Y') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
