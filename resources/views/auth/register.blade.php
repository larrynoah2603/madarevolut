<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Créer un compte</h1>
            <p class="text-sm text-gray-500">Rejoignez MadaRevolut en quelques étapes.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700" for="name">Nom complet</label>
                <input id="name" name="name" type="text" required autofocus
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
                <input id="email" name="email" type="email" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="phone_number">Téléphone</label>
                <input id="phone_number" name="phone_number" type="text" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="subscription_plan">Forfait</label>
                <select id="subscription_plan" name="subscription_plan"
                        class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="standard" @selected(old('subscription_plan', 'standard') === 'standard')>Standard (gratuit)</option>
                    <option value="plus" @selected(old('subscription_plan') === 'plus')>Plus</option>
                    <option value="premium" @selected(old('subscription_plan') === 'premium')>Premium</option>
                    <option value="metal" @selected(old('subscription_plan') === 'metal')>Metal</option>
                    <option value="ultra" @selected(old('subscription_plan') === 'ultra')>Ultra</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="password">Mot de passe</label>
                <input id="password" name="password" type="password" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="password_confirmation">Confirmer le mot de passe</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Créer mon compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-500">
            Déjà inscrit ?
            <a class="text-indigo-600 hover:underline" href="{{ route('login') }}">Se connecter</a>
        </p>
    </div>
</x-layouts.guest>
