<x-admin::layouts>
    <x-slot:title>
        {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}
    </x-slot:title>

    <x-admin::form
        {{-- :action="route('wc_customization.customization.store')" --}}
        enctype="multipart/form-data"
        method="POST"
    >

        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] . ' Settings' : __('Customization Sections Setting') }}
            </p>

            <div class="flex gap-x-[10px] items-center">
                <!-- Back Button -->
                <a  href="{{ route('wc_customization.admin.customization.pages.index', $page_slug) }}" 
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                >Back</a>

                <!-- Save Button -->
                {{-- <button
                    type="submit"
                    class="primary-button"
                >Save</button> --}}
            </div>        
        </div>

        

    </x-admin::form>

</x-admin::layouts>