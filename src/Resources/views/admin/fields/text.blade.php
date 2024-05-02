@php $required_keys = array( 'title', 'type', 'name' ) @endphp

@if( $field && is_array( $field ) && count( array_intersect_key( array_flip( $required_keys ), $field ) ) === count( $required_keys) )

<div class="mb-4">
    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium {{ $required_field }}">{{ $field[ 'title' ] }}</label>
    <input 
        type="{{ $field[ 'type' ] }}" 
        name="{{ $field_input_name }}" 
        class="w-full py-2.5 px-3 border rounded-md text-sm text-gray-600 dark:text-gray-300 transition-all hover:border-gray-400 dark:hover:border-gray-400 focus:border-gray-400 dark:focus:border-gray-400 dark:bg-gray-900 dark:border-gray-800" 
        id="{{ $field[ 'name' ] }}" 
        placeholder="{{ $field[ 'title' ] }}"
        value="{{ $section_field_val }}"
        {{ $required_field }}
    >
</div>

@endif