<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Bavix\Wallet\Models\Transaction;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'History';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('uuid')
                          ->label('Transaction ID')
                          ->grow(false)
                          ->description(fn(Transaction $record): string => $record->created_at),
                TextColumn::make('meta.message')
                          ->label('Description')
                          ->limit()
                          ->grow(),
                TextColumn::make('amount')
                          ->prefix(function (Transaction $record) {
                              $prefix = '+';
                              if ($record->type == Transaction::TYPE_WITHDRAW) {
                                  $prefix = '-';
                              }

                              return $prefix.'Rp';
                          })
                          ->numeric(decimalPlaces: 2)
                          ->color(function (Transaction $record) {
                              if ($record->type == Transaction::TYPE_DEPOSIT) {
                                  return 'success';
                              }
                          }),
            ])
            ->filters([
                //
            ])
            ->defaultSort('created_at', 'desc');
    }
}
