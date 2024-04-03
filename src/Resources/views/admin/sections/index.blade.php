<x-admin::layouts>
    <x-slot:title>
        {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] : __('Customization Sections') }}
    </x-slot:title>

    <x-admin::form
        :action="route('wc_customization.customization.store')"
        enctype="multipart/form-data"
        method="POST"
    >

        <div class="flex gap-4 justify-between items-center max-sm:flex-wrap">
            <p class="text-xl text-gray-800 dark:text-white font-bold">
                {{ ( $customization_section && isset( $customization_section[ 'title' ] ) ) ? $customization_section[ 'title' ] : __('Customization Sections') }}
            </p>

            <div class="flex gap-x-[10px] items-center">
                <!-- Back Button -->
                <a  href="{{ route('wc_customization.admin.customization.pages.index', $page_slug) }}" 
                    class="transparent-button hover:bg-gray-200 dark:hover:bg-gray-800 dark:text-white"
                >Back</a>

                <!-- Save Button -->
                <button
                    type="submit"
                    class="primary-button"
                >Save</button>
            </div>        
        </div>

        @if( $fields && count($fields) > 0 )
            
            @foreach( $fields as $field )

                @php $required_field = $field[ 'required' ] ? 'required' : ''; @endphp
                
                @if( $field[ 'type' ] == 'text' )

                    <!-- Text Box -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label class="{{ $required_field }}">
                            {{ $field[ 'title' ] }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            :type="$field[ 'type' ]"
                            :id="$field[ 'name' ]"
                            :name="$field[ 'name' ]"
                            :rules="$required_field"
                            :label="$field[ 'title' ]"
                        />

                        <x-admin::form.control-group.error control-name="$field[ 'name' ]" />
                    </x-admin::form.control-group>

                @endif

                @if( $field[ 'type' ] == 'select' )

                    <!-- Select Box -->
                    <x-admin::form.control-group class="flex-1 w-full">
                        <x-admin::form.control-group.label class="{{ $required_field }}">
                            {{ $field[ 'title' ] }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            :type="$field[ 'type' ]"
                            :id="$field[ 'name' ]"
                            class="cursor-pointer"
                            :name="$field[ 'name' ]"
                            :rules="$required_field"
                            {{-- value="products_and_description" --}}
                            :label="$field[ 'title' ]"
                        >
                            <!-- Options -->
                            <option value="">Select {{ $field[ 'title' ] }}</option>
                            @if( array_key_exists( 'options', $field) &&  $field[ 'options' ] && count( $field[ 'options' ] ) > 0 )
                                @foreach( $field[ 'options' ] as $option_key => $option_val )
                                    <option value="{{ $option_key }}">{{ $option_val }}</option>
                                @endforeach
                            @endif
                        </x-admin::form.control-group.control>

                        <x-admin::form.control-group.error control-name="$field[ 'name' ]" />
                    </x-admin::form.control-group>

                @endif

                @if( $field[ 'type' ] == 'textarea' )

                    <!-- Text Area -->
                    <x-admin::form.control-group>
                        <x-admin::form.control-group.label  class="{{ $required_field }}">
                            {{ $field[ 'title' ] }}
                        </x-admin::form.control-group.label>

                        <x-admin::form.control-group.control
                            :type="$field[ 'type' ]"
                            :id="$field[ 'name' ]"
                            :name="$field[ 'name' ]"
                            :rules="$required_field"
                            {{-- value="" --}}
                            :label="$field[ 'title' ]"
                        />

                        <x-admin::form.control-group.error control-name="$field[ 'name' ]" />
                    </x-admin::form.control-group>

                @endif

            @endforeach

        @endif

    </x-admin::form>

</x-admin::layouts>