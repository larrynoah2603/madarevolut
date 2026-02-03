<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Madarevolut</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <main class="min-h-screen flex items-center justify-center p-6">
        <section class="max-w-xl text-center space-y-6">
            <h1 class="text-3xl font-semibold">Bienvenue sur Madarevolut</h1>
            <p class="text-base">
                Le projet est prêt à démarrer. Lancez le serveur Laravel et commencez à développer.
            </p>
            <p class="text-sm text-gray-600">
                Accédez ensuite aux écrans Livewire via les routes prévues dans l'application.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center px-5 py-2 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 transition">
                    Se connecter
                </a>
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center px-5 py-2 rounded-lg border border-indigo-600 text-indigo-600 font-medium hover:bg-indigo-50 transition">
                    Créer un compte
                </a>
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center justify-center px-5 py-2 rounded-lg bg-gray-900 text-white font-medium hover:bg-gray-800 transition">
                    Accéder au dashboard
                </a>
            </div>
        </section>
    </main>
</body>
</html>
