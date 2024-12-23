<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Actions\Account\MakeTransfer;
use App\Events\ClientMakeDeposit;
use App\Filament\Resources\AccountResource\Pages\ViewAccount;
use App\Models\Account;
use Bavix\Wallet\Models\Transaction;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\Alignment;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\QueryBuilder;
use Filament\Tables\Filters\QueryBuilder\Constraints\DateConstraint;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Thunk\Verbs\Exceptions\EventNotValid;

class TransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = 'History';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                Grid::make([
                    'default' => 2,
                    'md'      => 3,
                ])->schema([
                    TextColumn::make('uuid')
                              ->label('Transaction ID')
                              ->copyable()
                              ->searchable()
                              ->lineClamp(1)
                              ->tooltip(function (TextColumn $column): ?string {
                                  return $column->getState();
                              })
                              ->grow(false)
                              ->description(fn(Transaction $record
                              ): string => $record->created_at->format('M j, Y H:i:s')),
                    TextColumn::make('amount')
                              ->alignment(Alignment::End)
                              ->prefix(function (Transaction $record) {
                                  $prefix = '+';
                                  if ($record->type == Transaction::TYPE_WITHDRAW) {
                                      $prefix = '-';
                                  }

                                  return $prefix.'Rp';
                              })
                              ->color(function (Transaction $record) {
                                  if ($record->type == Transaction::TYPE_DEPOSIT) {
                                      return 'success';
                                  }
                              })
                              ->formatStateUsing(function ($state) {
                                  return number_format(abs($state), 2);
                              })
                              ->hiddenFrom('md'),
                    TextColumn::make('meta.message')
                              ->label('Description')
                              ->limit()
                              ->grow()
                              ->columnSpan([
                                  'default' => 2,
                                  'md'      => 1,
                              ]),
                    TextColumn::make('amount')
                              ->alignment(Alignment::End)
                              ->prefix(function (Transaction $record) {
                                  $prefix = '+';
                                  if ($record->type == Transaction::TYPE_WITHDRAW) {
                                      $prefix = '-';
                                  }

                                  return $prefix.'Rp';
                              })
                              ->color(function (Transaction $record) {
                                  if ($record->type == Transaction::TYPE_DEPOSIT) {
                                      return 'success';
                                  }
                              })
                              ->formatStateUsing(function ($state) {
                                  return number_format(abs($state), 2);
                              })
                              ->visibleFrom('md')
                              ->summarize([
                                  Summarizer::make()
                                            ->label('Deposit')
                                            ->prefix('Rp')
                                            ->numeric(2)
                                            ->using(function ($query) {
                                                return $query->where('type', Transaction::TYPE_DEPOSIT)
                                                             ->sum('amount');
                                            }),
                                  Summarizer::make()
                                            ->label('Withdraw')
                                            ->prefix('Rp')
                                            ->numeric(2)
                                            ->using(function ($query) {
                                                return $query->where('type', Transaction::TYPE_WITHDRAW)
                                                             ->sum('amount');
                                            }),
                              ]),
                ]),
            ])
            ->headerActions($this->headerActions())
            ->filters([
                SelectFilter::make('created_at')
                            ->label('Transaction date')
                            ->options([
                                'd'   => 'Today',
                                'd-1' => 'Yesterday',
                                'd-7' => 'Last 7 days',
                                'mtd' => 'This month',
                                'm-1' => 'Last month',
                                'ytd' => 'This year',
                                'y-1' => 'Last year',
                            ])
                            ->query(function (Builder $query, $data) {
                                $filter = $data['value'];

                                return match ($filter) {
                                    'd' => $query->whereDate('created_at',
                                        '>=',
                                        today()
                                    ),
                                    'd-1' => $query->whereBetween('created_at', [
                                        today(),
                                        today()->subday(),
                                    ]),
                                    'd-7' => $query->whereDate('created_at',
                                        '>=',
                                        today()->subdays(7)
                                    ),
                                    'mtd' => $query->whereDate('created_at',
                                        '>=',
                                        today()->startOfMonth()
                                    ),
                                    'm-1' => $query->whereBetween('created_at', [
                                        today()->subMonth()->startOfMonth(),
                                        today()->subMonth()->endOfMonth(),
                                    ]),
                                    'ytd' => $query->whereDate('created_at',
                                        '>=',
                                        today()->startOfYear()
                                    ),
                                    'y-1' => $query->whereBetween('created_at', [
                                        today()->subYear()->startOfYear(),
                                        today()->subYear()->endOfYear(),
                                    ]),
                                    default => $query
                                };
                            })
                            ->default('mtd'),
                QueryBuilder::make()
                            ->constraints([
                                DateConstraint::make('created_at'),
                            ]),
            ])
            ->groups([
                Group::make('created_at')
                     ->label('Date')
                     ->collapsible()
                     ->date()
                     ->orderQueryUsing(function ($query) {
                         return $query->latest();
                     }),
            ])
            ->groupingDirectionSettingHidden()
            ->defaultSort('created_at', 'desc');
    }

    private function headerActions(): array
    {
        return [
            Action::make('create_transfer')
                  ->color('gray')
                  ->requiresConfirmation()
                  ->form([
                      Select::make('destination_account_id')
                            ->label('Destination')
                            ->native(false)
                            ->required()
                            ->preload()
                            ->options(
                                function () {
                                    $accounts = Account::owner()
                                                       ->whereNot('id', $this->getOwnerRecord()->id)
                                                       ->get();
                                    $options = [];
                                    foreach ($accounts as $account) {
                                        $groupName = $account->accountType->dropdownDisplay;
                                        $options[$groupName][$account->id] = $account->name;
                                    }

                                    return $options;
                                }
                            ),
                      TextInput::make('amount')
                               ->numeric()
                               ->mask(RawJs::make('$money($input)'))
                               ->stripCharacters(',')
                               ->required()
                               ->prefix('Rp')
                               ->hintAction(
                                   FormAction::make('all_amount')
                                             ->label(function () {
                                                 return number_format($this->getOwnerRecord()->balance);
                                             })
                                             ->action(function (Set $set, $state) {
                                                 $set(
                                                     'amount',
                                                     number_format($this->getOwnerRecord()->balance)
                                                 );
                                             })
                               ),
                  ])
                  ->action(function (array $data): void {
                      $account = $this->getOwnerRecord();
                      $destination = Account::find($data['destination_account_id']);
                      (new MakeTransfer($account, $destination))->handle($data['amount'], 'Transfer funds');
                      Notification::make()
                                  ->title('Transfer successfully')
                                  ->success()
                                  ->send();
                      $this->redirect(route(ViewAccount::getRouteName(), $account->id));
                  }),
            //Action::make('make_deposit')
            //      ->requiresConfirmation()
            //      ->color('gray')
            //      ->form([
            //          TextInput::make('amount')
            //                   ->numeric()
            //                   ->maxValue(1000000)
            //                   ->mask(RawJs::make('$money($input)'))
            //                   ->stripCharacters(',')
            //                   ->required()
            //                   ->prefix('Rp'),
            //      ])
            //      ->action(function (array $data): void {
            //          $account = $this->getOwnerRecord();
            //          //(new MakeDeposit($account))->handle($data['amount'], 'Client deposit');
            //          try {
            //              ClientMakeDeposit::fire(
            //                  account_id: $account->id,
            //                  amount: (int) $data['amount']
            //              );
            //          } catch (EventNotValid $e) {
            //              Notification::make()
            //                          ->title($e->getMessage())
            //                          ->danger()
            //                          ->send();
            //
            //              return;
            //          }
            //          Notification::make()
            //                      ->title('Deposit successfully')
            //                      ->success()
            //                      ->send();
            //          $this->redirect(route(ViewAccount::getRouteName(), $account->id));
            //      }),
        ];
    }
}
