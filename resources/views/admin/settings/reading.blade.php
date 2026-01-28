@extends('admin.components.admin')

@section('header')
    Reading Settings
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">


        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- Reading Settings -->
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Your homepage displays</label>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="show_on_front" value="posts" {{ ($settings['show_on_front'] ?? 'posts') == 'posts' ? 'checked' : '' }}
                            onclick="togglePageOptions(false)">
                        <span class="ml-2">Your latest posts</span>
                    </label>
                </div>
                <div class="mt-2">
                    <label class="inline-flex items-center">
                        <input type="radio" class="form-radio" name="show_on_front" value="page" {{ ($settings['show_on_front'] ?? '') == 'page' ? 'checked' : '' }}
                            onclick="togglePageOptions(true)">
                        <span class="ml-2">A static page (select below)</span>
                    </label>
                </div>
            </div>

            <div id="page_options" class="{{ ($settings['show_on_front'] ?? '') == 'page' ? '' : 'hidden' }} ml-6">
                <div class="mb-4">
                    <label for="page_on_front" class="block text-gray-700 text-sm font-bold mb-2">Homepage:</label>
                    <select name="page_on_front" id="page_on_front"
                        class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">- Select -</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ ($settings['page_on_front'] ?? '') == $page->id ? 'selected' : '' }}>{{ $page->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="page_for_posts" class="block text-gray-700 text-sm font-bold mb-2">Posts page:</label>
                    <select name="page_for_posts" id="page_for_posts"
                        class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="">- Select -</option>
                        @foreach($pages as $page)
                            <option value="{{ $page->id }}" {{ ($settings['page_for_posts'] ?? '') == $page->id ? 'selected' : '' }}>{{ $page->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between mt-8">
                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    type="submit">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePageOptions(show) {
            const options = document.getElementById('page_options');
            if (show) {
                options.classList.remove('hidden');
            } else {
                options.classList.add('hidden');
            }
        }
    </script>
@endsection