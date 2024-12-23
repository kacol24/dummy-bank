<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;

class DailyCheckIn extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    protected static ?int $sort = -10;

    protected static string $view = 'filament.widgets.daily-check-in';

    public function checkInAction(): Action
    {
        return Action::make('checkIn')
                     ->disabledForm()
                     ->modalHeading('Daily Check-in')
                     ->modalSubmitActionLabel('Check in')
                     ->form([
                         Wizard::make()
                               ->skippable()
                               ->startOnStep(5)
                               ->steps([
                                   Step::make('Day 1'),
                                   Step::make('Day 2'),
                                   Step::make('Day 3'),
                                   Step::make('Day 4'),
                                   Step::make('Day 5'),
                                   Step::make('Day 6'),
                                   Step::make('Day 7'),
                               ])
                               ->previousAction(function (\Filament\Forms\Components\Actions\Action $action) {
                                   return $action->extraAttributes(['x-show' => 'false']);
                               })
                               ->nextAction(function (\Filament\Forms\Components\Actions\Action $action) {
                                   return $action->extraAttributes(['x-show' => 'false']);
                               }),
                     ]);
    }
}
