<section class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <!-- Judul kartu -->
    <div class="px-5 py-4 sm:px-6 border-b border-slate-100 flex items-center justify-between gap-3">
        <div>
            <h4 class="text-[15px] font-bold text-slate-800">
                {{ __('Update Password') }}
            </h4>
            <p class="text-sm text-slate-500 mt-0.5">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>
        </div>
    </div>

    <div class="p-5 sm:p-6">
        <form method="post" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Current Password') }}</label>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                    class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                    class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="update_password_password_confirmation" class="block text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                    class="w-full h-10 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition" />
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4 pt-5 border-t border-slate-100">
                <button type="submit"
                    class="inline-flex items-center justify-center gap-1.5 h-10 px-4 rounded-lg bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold shadow-sm transition whitespace-nowrap">{{ __('Save') }}</button>

                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-slate-500"
                    >{{ __('Saved.') }}</p>
                @endif
            </div>
        </form>
    </div>
</section>
