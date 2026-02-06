@if(Auth::check() && !request()->has('customize_preview'))
    <!-- Admin Bar Wrapper -->
    <div id="wp-admin-bar"
        style="position: fixed; top: 0; left: 0; width: 100%; height: 32px; background-color: #1d2327; color: #f0f0f1; z-index: 99999; display: flex; align-items: center; justify-content: space-between; padding: 0 10px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen-Sans, Ubuntu, Cantarell, 'Helvetica Neue', sans-serif; font-size: 13px; box-sizing: border-box; font-weight: 400;">

        <!-- Left Side: Site & Links -->
        <div style="display: flex; height: 100%; align-items: center;">

            <!-- Site Name Dropdown -->
            <div class="ab-item-wrapper" style="position: relative; height: 100%; display: flex; align-items: center;">
                <a href="{{ Request::is(get_admin_prefix() . '*') ? url('/') : route('admin.dashboard') }}" class="ab-item"
                    style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 5px; height: 32px; padding: 0 10px;"
                    onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                    <span class="dashicons-home" style="margin-right: 2px;">🏠</span>
                    <span class="ab-site-name"
                        style="font-weight: 600;">{{ \App\Models\Setting::get('site_title', 'BxCode CMS') }}</span>
                </a>

                <!-- Dropdown -->
                <div class="ab-sub-wrapper"
                    style="display: none; position: absolute; top: 32px; left: 0; background-color: #32373c; min-width: 180px; box-shadow: 0 3px 5px rgba(0,0,0,0.2);">

                    @if(Request::is(get_admin_prefix() . '*'))
                        <a href="{{ url('/') }}" class="ab-sub-item"
                            style="display: block; color: #b0b0b0; text-decoration: none; padding: 8px 12px; font-size: 13px;">Visit
                            Site</a>
                    @else
                        <a href="{{ route('admin.dashboard') }}" class="ab-sub-item"
                            style="display: block; color: #b0b0b0; text-decoration: none; padding: 8px 12px; font-size: 13px;">Dashboard</a>
                    @endif

                    <a href="{{ route('admin.themes.index') }}" class="ab-sub-item"
                        style="display: block; color: #b0b0b0; text-decoration: none; padding: 8px 12px; font-size: 13px;">Themes</a>
                    <a href="{{ route('admin.posts.index') }}" class="ab-sub-item"
                        style="display: block; color: #b0b0b0; text-decoration: none; padding: 8px 12px; font-size: 13px;">Menus</a>
                </div>
            </div>

            <!-- New Content (+ New) -->
            <div class="ab-item-wrapper" style="position: relative; height: 100%; display: flex; align-items: center;">
                <a href="{{ route('admin.posts.create') }}" class="ab-item"
                    style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 5px; height: 32px; padding: 0 10px;"
                    onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                    <span>+ New</span>
                </a>
                <div class="ab-sub-wrapper"
                    style="display: none; position: absolute; top: 32px; left: 0; background-color: #32373c; min-width: 150px; box-shadow: 0 3px 5px rgba(0,0,0,0.2);">
                    <a href="{{ route('admin.posts.create') }}" class="ab-sub-item"
                        style="display: block; color: #b0b0b0; text-decoration: none; padding: 6px 12px;">Post</a>
                    <a href="{{ route('admin.posts.create', ['type' => 'page']) }}" class="ab-sub-item"
                        style="display: block; color: #b0b0b0; text-decoration: none; padding: 6px 12px;">Page</a>

                    {{-- Active Custom Post Types --}}
                    {{-- Active Custom Post Types --}}
                    @if(\Illuminate\Support\Facades\Schema::hasTable('custom_post_types'))
                        @foreach(\Illuminate\Support\Facades\DB::table('custom_post_types')->where('active', 1)->get() as $cpt)
                            @php $settings = json_decode($cpt->settings, true) ?? []; @endphp
                            @if(isset($settings['show_in_admin_bar']) && $settings['show_in_admin_bar'])
                                <a href="{{ route('admin.posts.create', ['type' => $cpt->key]) }}" class="ab-sub-item"
                                    style="display: block; color: #b0b0b0; text-decoration: none; padding: 6px 12px;">
                                    {{ $cpt->singular_label }}
                                </a>
                            @endif
                        @endforeach
                    @endif

                    <a href="{{ route('admin.users.create') }}" class="ab-sub-item"
                        style="display: block; color: #b0b0b0; text-decoration: none; padding: 6px 12px; border-top: 1px solid #464b50; margin-top:2px;">User</a>
                </div>
            </div>

            <!-- Customize Link (Frontend Only) -->
            @if(!Request::is(get_admin_prefix() . '*'))
            <div class="ab-item-wrapper" style="position: relative; height: 100%; display: flex; align-items: center;">
                <a href="{{ route('admin.customize', ['url' => url()->current()]) }}" class="ab-item"
                    style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 5px; height: 32px; padding: 0 10px;"
                    onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                    <span>🖌️ Customize</span>
                </a>
            </div>
            @endif



            <!-- Edit Post Link (Frontend Only) -->
            @if(isset($post) && !Request::is(get_admin_prefix() . '*'))
                <a href="{{ route('admin.posts.edit', ['post' => $post->id]) }}" class="ab-item"
                    style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 5px; height: 32px; padding: 0 10px;"
                    onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                    onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                    <span>✏️ Edit {{ ucfirst($post->type) }}</span>
                </a>
            @endif

            <!-- Cache Clear Link -->
            <a href="{{ route('admin.cache.clear') }}" class="ab-item"
                style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 5px; height: 32px; padding: 0 10px;"
                onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                <span>🗂️ Clear Cache</span>
            </a>

            @if(isset($currentTemplate))
                <div
                    style="margin-left: 10px; font-size: 11px; color: #8c8f94; border-left: 1px solid #3c434a; padding-left: 10px; line-height: 32px;">
                    <span class="dashicons-admin-appearance" style="margin-right: 4px;">🎨</span>
                    {{ basename(str_replace('.', '/', $currentTemplate)) }}.blade.php
                </div>
            @endif

        </div>

        <!-- Right Side: User Profile -->
        <div class="ab-item-wrapper" style="position: relative; height: 100%; display: flex; align-items: center;">
            <a href="{{ route('admin.profile.edit') }}" class="ab-item"
                style="color: #f0f0f1; text-decoration: none; display: flex; align-items: center; gap: 8px; height: 32px; padding: 0 10px;"
                onmouseover="this.style.backgroundColor='#3c434a'; this.style.color='#72aee6'"
                onmouseout="this.style.backgroundColor='transparent'; this.style.color='#f0f0f1'">
                <span>Howdy, {{ Auth::user()->name }}</span>
                <!-- Avatar -->
                @if(Auth::user()->avatar_url)
                    <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"
                        style="width: 18px; height: 18px; border-radius: 2px; object-fit: cover;">
                @else
                    <div
                        style="width: 18px; height: 18px; background: #646970; border-radius: 2px; display: flex; align-items: center; justify-content: center; color: white; font-size: 10px; font-weight: bold;">
                        {{ \Illuminate\Support\Str::substr(Auth::user()->name, 0, 1) }}
                    </div>
                @endif
            </a>
            <div class="ab-sub-wrapper"
                style="display: none; position: absolute; top: 32px; right: 0; background-color: #32373c; min-width: 160px; box-shadow: 0 3px 5px rgba(0,0,0,0.2);">
                <a href="{{ route('admin.profile.edit') }}" class="ab-sub-item"
                    style="display: block; color: #b0b0b0; text-decoration: none; padding: 8px 12px;  border-bottom: 1px solid #464b50;">Edit
                    Profile</a>
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="ab-sub-item"
                        style="display: block; w-full; text-align: left; background:none; border:none;  color: #b0b0b0; text-decoration: none; padding: 8px 12px; width: 100%; cursor: pointer;">Log
                        Out</button>
                </form>
            </div>
        </div>

    </div>




    @if(!Request::is(get_admin_prefix() . '*'))
    <div id="customize-sidebar"
        style="position: fixed; top: 32px; left: 0; bottom: 0; width: 300px; background: #fff; z-index: 100000; transform: translateX(-100%); transition: transform 0.3s ease; box-shadow: 2px 0 5px rgba(0,0,0,0.1); border-right: 1px solid #ddd; display: flex; flex-direction: column;">

        <div
            style="padding: 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; background: #f0f0f1;">
            <h3 style="margin: 0; font-size: 14px; font-weight: 600; color: #1d2327;">Theme Customizer</h3>
            <button onclick="toggleCustomizer(event)"
                style="background: none; border: none; font-size: 18px; cursor: pointer; color: #666;">&times;</button>
        </div>

        <div style="padding: 20px; flex: 1; overflow-y: auto;">
            <p style="color: #666; font-size: 13px;">Use this panel to customize your theme settings in real-time.</p>

            <div style="margin-top: 20px;">
                <!-- Placeholder Controls -->
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #333;">Site
                        Title</label>
                    <input type="text" value="{{ \App\Models\Setting::get('site_title') }}"
                        style="width: 100%; padding: 5px; border: 1px solid #ccc; font-size: 13px; color: #333;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label
                        style="display: block; font-size: 12px; font-weight: 600; margin-bottom: 5px; color: #333;">Tagline</label>
                    <input type="text" value="{{ \App\Models\Setting::get('tagline') }}"
                        style="width: 100%; padding: 5px; border: 1px solid #ccc; font-size: 13px; color: #333;">
                </div>

                <div style="margin-top: 20px;">
                    <button
                        style="background: #2271b1; color: white; border: none; padding: 6px 12px; border-radius: 3px; font-size: 13px; cursor: pointer;">Save
                        Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomizer(e) {
            if (e) e.preventDefault();
            const sidebar = document.getElementById('customize-sidebar');
            if (sidebar.style.transform === 'translateX(0px)') {
                sidebar.style.transform = 'translateX(-100%)';
            } else {
                sidebar.style.transform = 'translateX(0px)';
            }
        }
    </script>
    @endif
    <style>
        /* Body Adjustment for Fixed Bar */
        html {
            margin-top: 32px !important;
        }

        /* Reset Box Sizing inside bar to prevent theme bleed */
        #wp-admin-bar * {
            box-sizing: border-box;
            line-height: normal;
        }

        /* Hover logic for submenus */
        .ab-item-wrapper:hover .ab-sub-wrapper {
            display: block !important;
        }

        .ab-sub-item:hover {
            color: #72aee6 !important;
        }
    </style>
@endif