<x-admin::layouts>
    <x-slot:title>
        {{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : __('Customization Pages') }}
    </x-slot:title>

    <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
        <p class="text-xl text-gray-800 dark:text-white font-bold">
            {{ ( $customization_page && isset( $customization_page[ 'title' ] ) ) ? $customization_page[ 'title' ] : __('Customization Pages') }}
        </p>

        <div class="flex gap-x-[10px] items-center">
            <a
                href="{{ route('wc_customization.admin.customization.index') }}" 
                class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                
            >Back</a>
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
            @if( $sections && count($sections) > 0 )
                @foreach( $sections as $section )
                    <x-admin::table.tbody.tr>
                        <x-admin::table.td>{{ $section['title'] }}</x-admin::table.td>
                        <x-admin::table.td>{{ $section['slug'] }}</x-admin::table.td>
                        <x-admin::table.td>
                            <a href="{{ route( 'wc_customization.admin.customization.sections.index', [ $page_slug, $section['slug'] ] ) }}">
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