<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmployeeGender;
use App\Enums\EmployeeStatus;
use App\Enums\MaritalStatus;
use App\Enums\SalaryCurrency;
use App\Models\CompanyBranch;
use App\Models\CompanyDepartment;
use App\Models\EmployeeAttachment;
use App\Models\EmployeePhoto;
use Filament\Actions\Action;
use Filament\Forms\Components\BaseFileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Livewire;
use Throwable;

class EmployeeEditForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('employee_profile_tabs')
                    ->tabs([
                        Tab::make('Información personal')
                            ->schema(self::personalTabSchema()),
                        Tab::make('Salario mensual')
                            ->schema(self::salaryTabSchema()),
                        Tab::make('Puesto y ubicación')
                            ->schema(self::jobTabSchema()),
                        Tab::make('Información bancaria')
                            ->schema(self::bankingTabSchema()),
                        Tab::make('Descuentos / IMSS')
                            ->schema(self::imssTabSchema()),
                        Tab::make('Vacaciones')
                            ->schema(self::vacationTabSchema()),
                        Tab::make('Archivos adjuntos')
                            ->schema(self::attachmentsTabSchema()),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    /** @return array<int, mixed> */
    protected static function personalTabSchema(): array
    {
        return [
            Section::make('Fotografías')
                ->description('Máximo 3 fotos. Haz clic en una imagen para verla en grande.')
                ->schema([
                    Repeater::make('photos')
                        ->relationship('photos')
                        ->label('Fotos')
                        ->maxItems(3)
                        ->grid(['default' => 1, 'sm' => 3])
                        ->compact()
                        ->itemHeaders(true)
                        ->itemLabel(fn (): ?string => null)
                        ->extraItemActions(self::driveTileExtraActions(canRename: false, actionName: 'managePhotoDriveItem'))
                        ->deleteAction(fn (Action $action) => $action->hidden())
                        ->reorderable()
                        ->orderColumn('sort_order')
                        ->defaultItems(0)
                        ->itemNumbers(false)
                        ->addActionLabel('Agregar foto')
                        ->extraAttributes(['class' => 'employee-drive-grid employee-photos-repeater'])
                        ->schema([
                            ViewField::make('photo_preview')
                                ->hiddenLabel()
                                ->view('filament.forms.employee-drive-tile')
                                ->dehydrated(false)
                                ->viewData(fn (Get $get, ?Model $record): array => self::driveTileViewData(
                                    $get,
                                    $record,
                                    kind: 'photo',
                                    pathKey: 'path',
                                )),
                            self::employeePhotoUpload(
                                FileUpload::make('path')
                                    ->directory(fn (): string => self::storagePath('photos'))
                                    ->live()
                                    ->required()
                                    ->hidden(fn (Get $get): bool => filled(self::normalizeUploadPath($get('path'))))
                            ),
                        ])
                        ->columnSpanFull(),
                ]),
            Section::make('Identificación y contacto')
                ->columns(2)
                ->schema([
                    TextInput::make('first_name')->label('Nombre(s)')->required()->maxLength(255),
                    TextInput::make('last_name')->label('Apellido paterno')->required()->maxLength(255),
                    TextInput::make('second_last_name')->label('Apellido materno')->maxLength(255),
                    TextInput::make('curp')->label('CURP')->maxLength(18),
                    TextInput::make('rfc')->label('RFC')->maxLength(13),
                    TextInput::make('nss')->label('NSS')->maxLength(20),
                    DatePicker::make('birth_date')->label('Fecha de nacimiento')->native(false),
                    Select::make('gender')->label('Género')->options(EmployeeGender::class)->native(false),
                    Select::make('marital_status')->label('Estado civil')->options(MaritalStatus::class)->native(false),
                    TextInput::make('email')->label('Correo')->email()->maxLength(255),
                    TextInput::make('phone')->label('Teléfono')->tel()->maxLength(30),
                    Textarea::make('address')->label('Domicilio')->rows(3)->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function salaryTabSchema(): array
    {
        return [
            Section::make('Compensación mensual')
                ->columns(2)
                ->schema([
                    TextInput::make('monthly_salary')
                        ->label('Salario mensual')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),
                    Select::make('salary_currency')
                        ->label('Moneda')
                        ->options(SalaryCurrency::class)
                        ->default(SalaryCurrency::Mxn)
                        ->native(false)
                        ->required(),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function jobTabSchema(): array
    {
        return [
            Section::make('Asignación laboral')
                ->columns(2)
                ->schema([
                    TextInput::make('employee_number')->label('Número de empleado')->maxLength(50),
                    TextInput::make('job_title')->label('Puesto')->maxLength(255),
                    Select::make('company_branch_id')
                        ->label('Sucursal')
                        ->options(fn (Component $livewire): array => self::branchOptions($livewire))
                        ->searchable()
                        ->native(false),
                    Select::make('company_department_id')
                        ->label('Departamento')
                        ->options(fn (Component $livewire): array => self::departmentOptions($livewire))
                        ->searchable()
                        ->native(false),
                    Select::make('status')
                        ->label('Estatus')
                        ->options(EmployeeStatus::class)
                        ->required()
                        ->native(false),
                    DatePicker::make('hire_date')->label('Fecha de ingreso')->native(false),
                    DatePicker::make('termination_date')->label('Fecha de baja')->native(false),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function bankingTabSchema(): array
    {
        return [
            Section::make('Datos bancarios')
                ->columns(2)
                ->schema([
                    TextInput::make('bank_name')->label('Banco')->maxLength(255),
                    TextInput::make('bank_account_number')->label('Número de cuenta')->maxLength(50),
                    TextInput::make('bank_card_number')->label('Número de tarjeta')->maxLength(50),
                    TextInput::make('bank_clabe')->label('CLABE interbancaria')->length(18)->maxLength(18),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function imssTabSchema(): array
    {
        return [
            Section::make('IMSS y descuentos')
                ->columns(2)
                ->schema([
                    TextInput::make('imss_daily_sbc')
                        ->label('SBC diario (IMSS)')
                        ->numeric()
                        ->minValue(0)
                        ->step(0.01),
                    Placeholder::make('nss_reference')
                        ->label('NSS registrado')
                        ->content(fn (Component $livewire): string => (string) ($livewire->getRecord()?->nss ?: '—')),
                    Textarea::make('payroll_discounts_notes')
                        ->label('Descuentos y observaciones')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('Incluye notas de IMSS, préstamos, pensión alimenticia u otros descuentos de nómina.'),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function vacationTabSchema(): array
    {
        return [
            Section::make('Control de vacaciones')
                ->columns(2)
                ->schema([
                    TextInput::make('vacation_days_per_year')
                        ->label('Días anuales')
                        ->numeric()
                        ->minValue(0)
                        ->integer(),
                    TextInput::make('vacation_days_used')
                        ->label('Días tomados')
                        ->numeric()
                        ->minValue(0)
                        ->integer()
                        ->default(0),
                    Placeholder::make('vacation_days_remaining_display')
                        ->label('Días restantes')
                        ->content(fn (Component $livewire): string => (string) ($livewire->getRecord()?->vacationDaysRemaining() ?? '—')),
                    Textarea::make('vacation_notes')
                        ->label('Notas')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int, mixed> */
    protected static function attachmentsTabSchema(): array
    {
        return [
            Section::make('Expediente digital')
                ->schema([
                    Repeater::make('attachments')
                        ->relationship('attachments')
                        ->label('Archivos')
                        ->defaultItems(0)
                        ->grid(['default' => 2, 'sm' => 3, 'lg' => 4])
                        ->compact()
                        ->itemHeaders(true)
                        ->itemLabel(fn (): ?string => null)
                        ->extraItemActions(self::driveTileExtraActions(actionName: 'manageAttachmentDriveItem'))
                        ->deleteAction(fn (Action $action) => $action->hidden())
                        ->itemNumbers(false)
                        ->addActionLabel('Subir archivo')
                        ->extraAttributes(['class' => 'employee-drive-grid employee-drive-files'])
                        ->schema([
                            ViewField::make('file_preview')
                                ->hiddenLabel()
                                ->view('filament.forms.employee-drive-tile')
                                ->dehydrated(false)
                                ->viewData(fn (Get $get, ?Model $record): array => self::driveTileViewData(
                                    $get,
                                    $record,
                                    kind: 'file',
                                    pathKey: 'file_path',
                                )),
                            Hidden::make('title'),
                            Hidden::make('original_name'),
                            self::attachmentFileUpload(
                                FileUpload::make('file_path')
                                    ->directory(fn (): string => self::storagePath('attachments'))
                                    ->live()
                                    ->required()
                                    ->hidden(fn (Get $get): bool => filled(self::normalizeUploadPath($get('file_path'))))
                            ),
                            Textarea::make('notes')
                                ->label('Notas')
                                ->rows(2)
                                ->hidden()
                                ->dehydrated(),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    /** @return array<int|string, string> */
    protected static function branchOptions(Component $livewire): array
    {
        $companyId = $livewire->getRecord()?->company_id;

        if (! $companyId) {
            return [];
        }

        return CompanyBranch::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /** @return array<int|string, string> */
    protected static function departmentOptions(Component $livewire): array
    {
        $companyId = $livewire->getRecord()?->company_id;

        if (! $companyId) {
            return [];
        }

        return CompanyDepartment::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    protected static function storagePath(string $suffix): string
    {
        $livewire = Livewire::current();
        $employeeId = method_exists($livewire, 'getRecord') ? $livewire->getRecord()?->getKey() : null;

        return 'employees/'.($employeeId ?? 'temp').'/'.$suffix;
    }

    protected static function employeePhotoUpload(FileUpload $field): FileUpload
    {
        return self::publicFileUpload(
            $field
                ->hiddenLabel()
                ->image()
                ->panelLayout('compact')
                ->panelAspectRatio('1:1')
                ->imagePreviewHeight('112')
                ->imageEditor()
                ->imageEditorViewportWidth(1280)
                ->imageEditorViewportHeight(960)
                ->imageEditorMode(1)
                ->extraAttributes(['class' => 'employee-drive-upload employee-drive-upload--photo'])
        );
    }

    protected static function attachmentFileUpload(FileUpload $field): FileUpload
    {
        return self::publicFileUpload(
            $field
                ->hiddenLabel()
                ->storeFileNamesIn('original_name')
                ->previewable(false)
                ->downloadable()
                ->openable()
                ->panelLayout('compact')
                ->extraAttributes(['class' => 'employee-drive-upload'])
        );
    }

    /**
     * @return array<Action>
     */
    protected static function driveTileExtraActions(bool $canRename = true, string $actionName = 'manageDriveItem'): array
    {
        $deleteItem = function (array $arguments, Repeater $component): void {
            $state = $component->getState();
            unset($state[$arguments['item']]);
            $component->state($state);
        };

        if (! $canRename) {
            return [
                Action::make($actionName)
                    ->icon(Heroicon::PencilSquare)
                    ->iconButton()
                    ->color('gray')
                    ->size(Size::Small)
                    ->tooltip('Opciones')
                    ->extraAttributes(['class' => 'employee-drive-tile__menu'])
                    ->requiresConfirmation()
                    ->modalIcon(Heroicon::Trash)
                    ->modalIconColor('danger')
                    ->modalHeading('Eliminar foto')
                    ->modalDescription('¿Deseas eliminar esta fotografía?')
                    ->modalSubmitActionLabel('Eliminar')
                    ->action($deleteItem),
            ];
        }

        return [
            Action::make($actionName)
                ->icon(Heroicon::PencilSquare)
                ->iconButton()
                ->color('gray')
                ->size(Size::Small)
                ->tooltip('Opciones')
                ->extraAttributes(['class' => 'employee-drive-tile__menu'])
                ->modalHeading('Editar archivo')
                ->modalSubmitActionLabel('Guardar')
                ->modalWidth(Width::Small)
                ->fillForm(function (array $arguments, Repeater $component): array {
                    $item = $component->getItemStateSnapshot($arguments['item']) ?? [];

                    return [
                        'title' => $item['title']
                            ?? $item['original_name']
                            ?? '',
                    ];
                })
                ->schema([
                    TextInput::make('title')
                        ->label('Nombre')
                        ->required()
                        ->maxLength(255),
                ])
                ->action(function (array $data, array $arguments, Repeater $component): void {
                    $state = $component->getState();
                    $itemKey = $arguments['item'];

                    if (! array_key_exists($itemKey, $state)) {
                        return;
                    }

                    $state[$itemKey]['title'] = $data['title'];
                    $component->state($state);
                })
                ->extraModalFooterActions([
                    Action::make('delete'.$actionName)
                        ->label('Eliminar')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Eliminar archivo')
                        ->modalSubmitActionLabel('Eliminar')
                        ->action($deleteItem),
                ]),
        ];
    }

    protected static function driveTileViewData(Get $get, ?Model $record, string $kind, string $pathKey): array
    {
        $path = self::normalizeUploadPath($get($pathKey));

        if (blank($path)) {
            $path = match (true) {
                $kind === 'file' && $record instanceof EmployeeAttachment => $record->file_path,
                $kind === 'photo' && $record instanceof EmployeePhoto => $record->path,
                default => null,
            };
        }

        $title = $get('title');
        $originalName = $get('original_name');

        if ($record instanceof EmployeeAttachment) {
            $title = filled($title) ? $title : $record->title;
            $originalName = filled($originalName) ? $originalName : $record->original_name;

            if (blank($title)) {
                $title = $record->displayName();
            }
        }

        $uploadedAt = $get('created_at') ?? $record?->getAttribute('created_at');

        return [
            'kind' => $kind,
            'layout' => 'full',
            'path' => $path,
            'title' => $title,
            'originalName' => $originalName,
            'uploadedAt' => $uploadedAt,
        ];
    }

    protected static function normalizeUploadPath(mixed $state): ?string
    {
        if (blank($state)) {
            return null;
        }

        if (is_string($state)) {
            return $state;
        }

        if (is_array($state)) {
            $first = reset($state);

            return is_string($first) ? $first : null;
        }

        return null;
    }

    protected static function publicFileUpload(FileUpload $field): FileUpload
    {
        return $field
            ->disk('public')
            ->visibility('public')
            ->getUploadedFileUsing(static function (BaseFileUpload $component, string $file, string|array|null $storedFileNames): ?array {
                $storage = Storage::disk('public');

                if (blank($file) || ! $storage->exists($file)) {
                    return null;
                }

                try {
                    $name = basename($file);

                    if (is_array($storedFileNames)) {
                        $name = $storedFileNames[$file] ?? $name;
                    }

                    if (is_string($storedFileNames) && filled($storedFileNames)) {
                        $name = $storedFileNames;
                    }

                    if (preg_match('/^[a-f0-9\-]{20,}\./i', $name) || strlen($name) > 48) {
                        $name = 'Archivo';
                    }

                    return [
                        'name' => $name,
                        'size' => $storage->size($file),
                        'type' => $storage->mimeType($file),
                        'url' => asset('storage/'.ltrim($file, '/')),
                    ];
                } catch (Throwable) {
                    return null;
                }
            });
    }
}
