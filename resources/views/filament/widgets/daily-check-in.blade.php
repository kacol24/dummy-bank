<x-filament-widgets::widget>
    <style>
        .fi-fo-wizard-step,
        .flex.items-center.justify-between.gap-x-3.px-6.pb-6 {
            display: none;
        }
    </style>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <h2 class="grid flex-1 text-base font-semibold leading-6 text-gray-950 dark:text-white">
                    {{ __('filament-panels::widgets/account-widget.welcome', ['app' => config('app.name')]) }}
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    asdjkl
                </p>
            </div>
            <div class="my-auto">
                {{ $this->checkInAction }}
                <x-filament-actions::modals/>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
