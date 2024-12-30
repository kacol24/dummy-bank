<?php

namespace App\Filament\Widgets;

use App\Events\CheckIn;
use App\States\DailyCheckInState;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\StaticAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard;
use Filament\Support\Enums\MaxWidth;
use Filament\Widgets\Widget;
use Thunk\Verbs\Exceptions\EventNotValid;

class DailyCheckIn extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = -10;

    protected static string $view = 'filament.widgets.daily-check-in';

    protected $checkInState;

    public $nextCheckIn = null;

    public function __construct()
    {
        $this->checkInState = DailyCheckInState::load(Filament::auth()->id());
        if (is_null($this->checkInState->last_checkin_at)) {
            return $this->nextCheckIn = 1;
        }
        if ($this->checkInState->last_checkin_at->isToday()) {
            return $this->nextCheckIn = $this->checkInState->checkin_count;
        }
        if ($this->checkInState->last_checkin_at->isYesterday()) {
            return $this->nextCheckIn = $this->checkInState->checkin_count + 1;
        }

        return $this->nextCheckIn = 1;
    }

    public function checkInAction(): Action
    {
        $iconColor = 'warning';
        $description = 'Are you sure you would like to do this?';
        $title = 'Daily Check-in';
        if (! is_null($this->checkInState->last_checkin_at) && $this->checkInState->last_checkin_at->isToday()) {
            $iconColor = 'success';
            $description = 'Come back tomorrow';
            $title = 'Completed!';
        }

        $checkedInCount = $this->checkInState->checkin_count;
        if (is_null($this->checkInState->last_checkin_at)) {
            $checkedInCount = 0;
        }
        if ($this->checkInState->last_checkin_at->diffInDays(today()) > 1) {
            $checkedInCount = 0;
        }

        return Action::make('checkIn')
                     ->disabledForm()
                     ->requiresConfirmation()
                     ->modalWidth(MaxWidth::FourExtraLarge)
                     ->modalHeading($title)
                     ->modalSubmitActionLabel('Check in')
                     ->modalSubmitAction(function (StaticAction $action) use ($iconColor) {
                         if ($iconColor == 'success') {
                             $action->hidden();
                         }
                     })
                     ->modalIcon('heroicon-o-check')
                     ->modalIconColor($iconColor)
                     ->modalDescription($description)
                     ->form([
                         CheckboxList::make('check_in')
                                     ->label(false)
                                     ->columns(7)
                                     ->default(range(0, $checkedInCount))
                                     ->options([
                                         1 => 'Day 1',
                                         2 => 'Day 2',
                                         3 => 'Day 3',
                                         4 => 'Day 4',
                                         5 => 'Day 5',
                                         6 => 'Day 6',
                                         7 => 'Day 7',
                                     ])
                                     ->descriptions([
                                         1 => 'Rp100,000',
                                         2 => 'Rp200,000',
                                         3 => 'Rp300,000',
                                         4 => 'Rp500,000',
                                         5 => 'Rp800,000',
                                         6 => 'Rp1,300,000',
                                         7 => 'Rp2,100,000',
                                     ])
                                     ->disableOptionWhen(fn(string $value): bool => $value <= $checkedInCount),
                     ])
                     ->action(function () {
                         try {
                             CheckIn::fire(user_id: filament()->auth()->id());
                         } catch (EventNotValid $e) {
                             Notification::make()
                                         ->title($e->getMessage())
                                         ->danger()
                                         ->send();

                             return;
                         }
                         Notification::make()
                                     ->title('Check-in successfully!')
                                     ->success()
                                     ->send();

                         $this->redirect(route(Dashboard::getRouteName()));
                     });
    }
}
