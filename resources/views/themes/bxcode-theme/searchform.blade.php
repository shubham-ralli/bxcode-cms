<form role="search" method="get" class="search-form flex gap-2" action="{{ route('frontend.search') }}">
    <label class="flex-grow">
        <span class="screen-reader-text sr-only">Search for:</span>
        <input type="search"
            class="search-field w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Search &hellip;" value="{{ request()->get('s') }}" name="s" />
    </label>
    <button type="submit"
        class="search-submit px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        Search
    </button>
</form>