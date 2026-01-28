<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install BxCode CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="h-full">
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <div
                class="mx-auto h-12 w-12 bg-indigo-500 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                B
            </div>

            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-white">Install BxCode CMS</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-md">

            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Installation Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul role="list" class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('install.store') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Database Section -->
                <div class="border-b border-gray-700 pb-4 mb-4">
                    <h3 class="text-lg font-medium leading-6 text-white mb-4">Database Configuration</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="db_name" class="block text-sm/6 font-medium text-gray-100">Database Name</label>
                            <div class="mt-2">
                                <input id="db_name" type="text" name="db_name" required
                                    class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                                    placeholder="e.g. laravel_cms">
                            </div>
                        </div>

                        <div>
                            <label for="db_username" class="block text-sm/6 font-medium text-gray-100">Database
                                Username</label>
                            <div class="mt-2">
                                <input id="db_username" type="text" name="db_username" required
                                    class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6"
                                    placeholder="root">
                            </div>
                        </div>

                        <div>
                            <label for="db_password" class="block text-sm/6 font-medium text-gray-100">Database
                                Password</label>
                            <div class="mt-2">
                                <input id="db_password" type="password" name="db_password"
                                    class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Section -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-white mb-4">Admin Account</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="admin_email" class="block text-sm/6 font-medium text-gray-100">Admin
                                Email</label>
                            <div class="mt-2">
                                <input id="admin_email" type="email" name="admin_email" required
                                    class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6">
                            </div>
                        </div>

                        <div>
                            <label for="admin_password" class="block text-sm/6 font-medium text-gray-100">Admin
                                Password</label>
                            <div class="mt-2">
                                <input id="admin_password" type="password" name="admin_password" required minlength="8"
                                    class="block w-full rounded-md bg-white/5 px-3 py-1.5 text-base text-white outline-1 -outline-offset-1 outline-white/10 placeholder:text-gray-500 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-500 sm:text-sm/6">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit"
                        class="flex w-full justify-center rounded-md bg-indigo-500 px-3 py-1.5 text-sm/6 font-semibold text-white hover:bg-indigo-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 shadow-lg shadow-indigo-500/20">
                        Install & Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>

</html>