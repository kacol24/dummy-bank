<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Models\Account;
use App\Models\AccountType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('account_type_id')
                      ->label('Account type')
                      ->required()
                      ->options(function () {
                          return AccountType::query()
                                            ->when(auth()->user()->accounts->count() == 0, function ($query) {
                                                return $query->where('is_default', true);
                                            })
                                            ->get()
                                            ->pluck('dropdownDisplay', 'id');
                      }),
                TextInput::make('name')
                         ->required(),
                //TextInput::make('account_number')
                //         ->maxLength(10),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_number')
                          ->copyable(),
                TextColumn::make('name'),
                TextColumn::make('accountType.dropdownDisplay'),
                TextColumn::make('balance')
                          ->prefix('Rp')
                          ->numeric(decimalPlaces: 2)
                          ->sortable(),
                TextColumn::make('last_transaction.created_at')
                          ->since()
                          ->dateTimeTooltip(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query){
                return $query->with('transactions');
            });
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Group::make()
                     ->columns(3)
                     ->columnSpan('full')
                     ->schema([
                         Group::make()
                              ->columns(['default' => 2])
                              ->columnSpan(['md' => 2])
                              ->schema([
                                  TextEntry::make('name'),
                                  TextEntry::make('created_at')
                                           ->sinceTooltip(),
                                  TextEntry::make('account_number')
                                           ->copyable(),
                                  TextEntry::make('user.name')
                                           ->label('Account holder'),
                                  TextEntry::make('accountType.name')
                                           ->label('Account type'),
                              ]),
                         Section::make()
                                ->columns(['default' => 2, 'md' => 1])
                                ->columnSpan(['md' => 1])
                                ->schema([
                                    TextEntry::make('balance')
                                             ->prefix('Rp')
                                             ->numeric(decimalPlaces: 2),
                                    TextEntry::make('timeDeposit.interest_rate')
                                             ->label('Interest rate')
                                             ->suffix('% p.a'),
                                    TextEntry::make('timeDeposit.friendlyPeriod')
                                             ->label('Disbursement')
                                             ->prefix('Every '),
                                ]),
                     ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [

        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'view'   => Pages\ViewAccount::route('/{record}'),
            'edit'   => Pages\EditAccount::route('/{record}/edit'),
        ];
    }
}
