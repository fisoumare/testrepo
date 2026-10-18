<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Inquiries\InquiryResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Inquiry;
use App\Models\Post;
use App\Models\Service;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $newInquiries = Inquiry::query()->where('status', Inquiry::STATUS_NEW)->count();
        $monthInquiries = Inquiry::query()->where('created_at', '>=', now()->startOfMonth())->count();

        return [
            Stat::make('Demandes nouvelles', $newInquiries)
                ->description('À traiter')
                ->descriptionIcon(Heroicon::OutlinedInboxArrowDown)
                ->color($newInquiries > 0 ? 'warning' : 'gray')
                ->url(InquiryResource::getUrl('index', ['filters' => ['status' => ['values' => [Inquiry::STATUS_NEW]]]])),
            Stat::make('Demandes du mois', $monthInquiries)
                ->description('Depuis le 1er '.now()->translatedFormat('F'))
                ->descriptionIcon(Heroicon::OutlinedCalendar)
                ->color('info')
                ->url(InquiryResource::getUrl()),
            Stat::make('Prestations actives', Service::query()->where('is_active', true)->count())
                ->description(Service::query()->count().' au catalogue')
                ->descriptionIcon(Heroicon::OutlinedServerStack)
                ->color('success')
                ->url(ServiceResource::getUrl()),
            Stat::make('Actualités publiées', Post::query()->published()->count())
                ->description(Post::query()->count().' au total')
                ->descriptionIcon(Heroicon::OutlinedNewspaper)
                ->color('primary')
                ->url(PostResource::getUrl()),
        ];
    }
}
