<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Video Call') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                <x-agora-call :uid="$uid" :name="$name" :token="$token" :app_id="$appId" :chatToken="$chatToken" />
            </div>
        </div>
    </div>
</x-app-layout>
