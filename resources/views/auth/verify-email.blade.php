<x-layouts.guest>
    <div class="bg-white shadow-xl rounded-2xl p-8 space-y-6 text-center">
        <h1 class="text-2xl font-bold">Vérification de l'email</h1>
        <p class="text-sm text-gray-500">
            Merci de vérifier votre adresse email via le lien envoyé. Si vous n'avez rien reçu,
            vous pouvez renvoyer un nouvel email.
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 text-white py-2 font-semibold hover:bg-indigo-700 transition">
                Renvoyer l'email de vérification
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-gray-500 hover:underline mt-4">Se déconnecter</button>
        </form>
    </div>
</x-layouts.guest>
