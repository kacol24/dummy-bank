<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Actions\Account\MakeDeposit;
use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use Bavix\Wallet\Models\Transaction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'History';

    public function isReadOnly(): bool
    {
        return false;
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
                TextColumn::make('created_at')
                          ->dateTime()
                          ->label('Transaction ID')
                          ->grow(false)
                          ->description(fn(Transaction $record): string => $record->uuid),
                TextColumn::make('meta.message')
                          ->label('Description')
                          ->limit()
                          ->grow(),
                TextColumn::make('amount')
                          ->alignment(Alignment::End)
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
            ->headerActions([
                Action::make('make_deposit')
                      ->requiresConfirmation()
                      ->color('gray')
                      ->form([
                          TextInput::make('amount')
                                   ->numeric()
                                   ->maxValue(1000000)
                                   ->mask(RawJs::make('$money($input)'))
                                   ->stripCharacters(',')
                                   ->required()
                                   ->prefix('Rp'),
                      ])
                      ->action(function (array $data): void {
                          (new MakeDeposit())->handle($this->getOwnerRecord(), $data['amount'], 'Client deposit');
                          Notification::make()
                                      ->title('Deposit successfully')
                                      ->success()
                                      ->send();
                          $this->redirect(route(ViewAccount::getRouteName(), $this->getOwnerRecord()->id));
                      }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
