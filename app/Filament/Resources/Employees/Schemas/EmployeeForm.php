<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Enums\EmployeeGender;
use App\Enums\EmployeeStatus;
use App\Enums\MaritalStatus;
use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Models\Employee;
use Livewire\Livewire;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Datos personales')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('first_name')
                            ->label('Nombre(s)')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('last_name')
                            ->label('Apellido paterno')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('second_last_name')
                            ->label('Apellido materno')
                            ->maxLength(255),
                        TextInput::make('curp')
                            ->label('CURP')
                            ->maxLength(18),
                        TextInput::make('rfc')
                            ->label('RFC')
                            ->maxLength(13),
                        TextInput::make('nss')
                            ->label('NSS')
                            ->maxLength(20),
                        DatePicker::make('birth_date')
                            ->label('Fecha de nacimiento')
                            ->native(false),
                        Select::make('gender')
                            ->label('Género')
                            ->options(EmployeeGender::class)
                            ->native(false)
                            ->searchable(),
                        Select::make('marital_status')
                            ->label('Estado civil')
                            ->options(MaritalStatus::class)
                            ->native(false)
                            ->searchable(),
                        TextInput::make('email')
                            ->label('Correo')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->maxLength(30),
                        Textarea::make('address')
                            ->label('Domicilio')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Datos laborales (básicos)')
                    ->columnSpanFull()
                    ->columns(2)
                    ->description('Al guardar podrás completar salario, puesto, banco y demás datos en la edición.')
                    ->schema([
                        TextInput::make('employee_number')
                            ->label('Número de empleado')
                            ->maxLength(50)
                            ->placeholder(fn (): ?string => self::suggestedEmployeeNumberPlaceholder()),
                        Select::make('status')
                            ->label('Estatus')
                            ->options(EmployeeStatus::class)
                            ->default(EmployeeStatus::Activo)
                            ->required()
                            ->native(false),
                        DatePicker::make('hire_date')
                            ->label('Fecha de ingreso')
                            ->native(false),
                    ]),
            ]);
    }

    protected static function suggestedEmployeeNumberPlaceholder(): ?string
    {
        $component = Livewire::current();

        if (! $component instanceof CreateEmployee) {
            return null;
        }

        $suggested = Employee::suggestedEmployeeNumberForCompany($component->company->getKey());

        return "Sugerido: {$suggested}";
    }
}