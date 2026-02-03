<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Connexion</h1>
            <p class="text-sm text-gray-500">Accédez à votre espace MadaRevolut.</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                <input id="email" name="email" type="email" required autofocus
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="password">Mot de passe</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-indigo-600">
                    <span class="ml-2">Se souvenir de moi</span>
                </label>
                <a class="text-indigo-600 hover:underline" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Se connecter
            </button>
        </form>

        <p class="text-center text-sm text-gray-500">
            Pas encore de compte ?
            <a class="text-indigo-600 hover:underline" href="{{ route('register') }}">Créer un compte</a>
        </p>
    </div>
</x-layouts.guest>
