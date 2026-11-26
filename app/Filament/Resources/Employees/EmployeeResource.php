<?php

namespace App\Filament\Resources\Employees;

use App\Enums\PanelNavigationGroup;
use App\Enums\UserType;
use App\Filament\Resources\Employees\Pages\BrowseCompanies;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|UnitEnum|null $navigationGroup = PanelNavigationGroup::RecursosHumanos;

    protected static ?string $modelLabel = 'empleado';

    protected static ?string $pluralModelLabel = 'Empleados';

    protected static ?string $navigationLabel = 'Empleados';

    protected static ?int $navigationSort = 3;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record instanceof Employee ? $record->fullName() : null;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => BrowseCompanies::route('/'),
            'list' => ListEmployees::route('/{company}'),
            'create' => CreateEmployee::route('/{company}/create'),
            'edit' => EditEmployee::route('/{company}/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user instanceof User && $user->user_type === UserType::Usuario && ! $user->hasUnrestrictedRhCompanyAccess()) {
            $query->whereIn(
                'company_id',
                $user->companies()->select('companies.id'),
            );
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
