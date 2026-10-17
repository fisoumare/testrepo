@props(['service', 'showCategory' => false])
<x-ui.unit :href="route('services.show', $service)" padding="p-5" class="group flex h-full flex-col gap-4 no-underline transition hover:-translate-y-0.5 hover:shadow-lg">
    <div class="flex items-start justify-between gap-3">
        <span class="label text-ardoise">{{ $service->number }}@if($showCategory) · {{ $service->category->name }}@endif</span>
        <span class="led led-off group-hover:led-on group-hover:bg-emerald-400 mt-0.5 transition" aria-hidden="true"></span>
    </div>
    <h3 class="text-[17px] font-semibold leading-snug text-navy-900 text-balance">{{ $service->name }}</h3>
    <p class="text-sm leading-relaxed text-ardoise line-clamp-3">{{ $service->summary }}</p>
    <div class="mt-auto flex items-end justify-between gap-3 pt-2 border-t border-navy-900/8">
        <x-ui.price :service="$service" size="sm" />
        <span class="label text-ardoise">{{ $service->unit }}</span>
    </div>
</x-ui.unit>
