<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Mot de passe oublié</h1>
            <p class="text-sm text-gray-500">Recevez un lien de réinitialisation par email.</p>
        </div>

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                <input id="email" name="email" type="email" required autofocus autocomplete="email"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Envoyer le lien
            </button>
        </form>

        <p class="text-center text-sm text-gray-500">
            <a class="text-indigo-600 hover:underline" href="{{ route('login') }}">Retour à la connexion</a>
        </p>
    </div>
</x-layouts.guest>
