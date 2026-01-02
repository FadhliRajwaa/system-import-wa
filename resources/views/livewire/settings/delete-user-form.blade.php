<section class="mt-8">
    <div class="relative mb-5">
        <flux:heading class="text-red-600 dark:text-red-500">{{ __('Delete Account') }}</flux:heading>
        <flux:subheading>{{ __('Permanently delete your account and all of its resources.') }}</flux:subheading>
    </div>

    <div class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/20 rounded-xl p-5">
        <p class="text-sm text-red-800 dark:text-red-200 mb-4">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>

        <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger" icon="trash">
                {{ __('Delete Account') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div class="text-center sm:text-left">
                <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 mb-4">
                    <flux:icon name="exclamation-triangle" class="h-6 w-6 text-red-600" />
                </div>
                <flux:heading size="lg">{{ __('Delete Account?') }}</flux:heading>
                <flux:subheading>
                    {{ __('Are you sure you want to delete your account? This action cannot be undone.') }}
                </flux:subheading>
            </div>

            <flux:input 
                wire:model="password" 
                :label="__('Password')" 
                type="password" 
                placeholder="Enter your password to confirm"
            />

            <div class="flex justify-end gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">{{ __('Delete Account') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
