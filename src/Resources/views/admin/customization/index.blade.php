<x-admin::layouts>
    <x-slot:title>
        {{ __('Customization') }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            {{ __('Customization') }}
        </p>

        <div class="flex gap-x-[10px] items-center">
            {{-- @if (bouncer()->hasPermission('blog.blogs.create'))
                <a href="{{ route('admin.blog.create') }}">
                    <div class="primary-button">
                        {{ __('Create Blog') }}
                    </div>
                </a>
            @endif --}}
        </div>        
    </div>

    <x-admin::table>
        <x-admin::table.thead>
            <x-admin::table.thead.tr>
                <x-admin::table.th>Title</x-admin::table.th>
                <x-admin::table.th>Slug</x-admin::table.th>
                <x-admin::table.th>Action</x-admin::table.th>
            </x-admin::table.thead.tr>
        </x-admin::table.thead>

        <x-admin::table.tbody>
            @if( is_array( $customizations ) && array_key_exists( 'pages', $customizations ) && $customizations[ 'pages' ] && count( $customizations[ 'pages' ] ) > 0 )
                @foreach( $customizations[ 'pages' ] as $page )
                    <x-admin::table.tbody.tr>
                        <x-admin::table.td>{{ $page['title'] }}</x-admin::table.td>
                        <x-admin::table.td>{{ $page['slug'] }}</x-admin::table.td>
                        <x-admin::table.td>
                            <a href="{{ route('wc_customization.admin.customization.pages.index', $page['slug']) }}">
                                <span class="icon-exit text-2xl"></span>
                            </a>
                        </x-admin::table.td>
                    </x-admin::table.thead.tr>
                @endforeach
            @else
                <x-admin::table.tbody.tr>
                    <x-admin::table.td colspan="3">No records found.</x-admin::table.td>
                </x-admin::table.thead.tr>
            @endif
            
        </x-admin::table.tbody>
    </x-admin::table>

</x-admin::layouts>