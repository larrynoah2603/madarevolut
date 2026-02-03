<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6">
        <div class="text-center">
            <h1 class="text-2xl font-bold">Authentification à deux facteurs</h1>
            <p class="text-sm text-gray-500">Entrez votre code ou utilisez un code de récupération.</p>
        </div>

        <form method="POST" action="{{ route('two-factor.login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700" for="code">Code</label>
                <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700" for="recovery_code">Code de récupération</label>
                <input id="recovery_code" name="recovery_code" type="text"
                       class="mt-1 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Valider
            </button>
        </form>
    </div>
</x-layouts.guest>
