<?php

namespace App\Providers\Filament;

use App\Enums\PanelNavigationGroup;
use App\Filament\Pages\EscritorioDashboard;
use App\Filament\Resources\CommercialQuotes\Pages\EditCommercialQuote;
use App\Filament\Resources\CommercialQuotes\Schemas\CommercialQuoteForm;
use App\Models\CommercialQuote;
use App\Support\CommercialQuotePanelUrls;
use App\Support\CommercialQuotePdfExporter;
use App\Support\PanelBrandColors;
use Filament\Enums\DatabaseNotificationsPosition;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Livewire\Livewire;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->login()
            ->brandName('DBPI')
            ->brandLogo(asset('img/svg/logo-dynacom.svg'))
            ->darkModeBrandLogo(asset('img/svg/logo-dynacom-white.svg'))
            ->brandLogoHeight('1.85rem')
            ->databaseNotifications(position: DatabaseNotificationsPosition::Topbar)
            ->colors([
                'primary' => PanelBrandColors::primary(),
            ])
            ->navigationGroups(collect(PanelNavigationGroup::sidebarGroups())
                ->mapWithKeys(fn (PanelNavigationGroup $group): array => [
                    $group->name => NavigationGroup::fromEnum($group),
                ])
                ->all())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                EscritorioDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::GLOBAL_SEARCH_AFTER,
                fn (): string => config('ai-assistant.enabled', true)
                    ? Blade::render('@livewire(\App\Livewire\AiAssistantChat::class)')
                    : '',
            )
            ->renderHook(CommercialQuoteForm::TABS_PDF_RENDER_HOOK, function (): string {
                $livewire = Livewire::current();

                if (! $livewire instanceof EditCommercialQuote) {
                    return '';
                }

                /** @var CommercialQuote $quote */
                $quote = $livewire->getRecord();

                return view('filament.resources.commercial-quotes.pdf-download-toolbar', [
                    'downloadUrls' => [
                        'cotizacion' => CommercialQuotePanelUrls::downloadPdf($quote, CommercialQuotePdfExporter::VARIANT_COTIZACION),
                        'margen' => CommercialQuotePanelUrls::downloadPdf($quote, CommercialQuotePdfExporter::VARIANT_MARGEN),
                        'todo' => CommercialQuotePanelUrls::downloadPdf($quote, CommercialQuotePdfExporter::VARIANT_TODO),
                    ],
                ])->render();
            }, EditCommercialQuote::class);
    }
}
