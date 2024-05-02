@php $required_keys = array( 'title', 'type', 'name' ) @endphp

@if( $field && is_array( $field ) && count( array_intersect_key( array_flip( $required_keys ), $field ) ) === count( $required_keys) )

<div class="mb-4">
    <div class="flex justify-between">
        <label 
            class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium 
                    {{ ( ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) 
                    ? "" 
                    : $required_field ) }}" 
            for="field_details[{{ $field[ 'name' ] }}]"
            > {{ $field[ 'title' ] }}
            <span class=""></span>
        </label>
    </div>
    <div class="flex justify-center items-center">
        <input 
            type="{{ $field[ 'type' ] }}" 
            name="{{ $field_input_name }}" 
            class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 dark:file:bg-gray-800 dark:file:dark:text-white dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
            id="{{ $field[ 'name' ] }}"
            {{ ( isset( $section_field_val ) && !empty( $section_field_val ) && !is_null( $section_field_val ) ) 
                ? "" 
                : $required_field
            }}
            {{ $multiple_option }}
        >
    </div>

    @if ( $section_field_val && is_array( $section_field_val ) && count( $section_field_val ) > 0 )

        <div class="flex ">

            @foreach ( $section_field_val as $file )
                
                @if( isset( $file ) && !empty( $file ) && !is_null( $file ) )
                
                    <a 
                        href="{{ $pre_file_url . $file }}" 
                        target="_blank"
                    >
                        <img 
                            src="{{ $pre_file_url . $file }}" 
                            class="relative mr-5 h-[33px] w-[33px] top-15 rounded-3 border-3 border-gray-500"
                        >
                    </a>

                @endif

            @endforeach

        </div>

    @endif

</div>

@endif