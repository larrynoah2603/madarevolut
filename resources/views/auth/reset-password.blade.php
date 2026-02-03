<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Réinitialiser le mot de passe</h1>
            <p class="text-sm text-gray-500">Choisissez un nouveau mot de passe sécurisé.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="email"
                       value="{{ old('email', $request->email) }}"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="password">Nouveau mot de passe</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="password_confirmation">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Mettre à jour
            </button>
        </form>
    </div>
</x-layouts.guest>
