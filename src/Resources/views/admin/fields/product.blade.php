@php $required_keys = array( 'title', 'type', 'name' ) @endphp

@if( $field && is_array( $field ) && count( array_intersect_key( array_flip( $required_keys ), $field ) ) === count( $required_keys) )

<div class="mb-4">
    <label class="flex gap-1 items-center mb-1.5 text-xs text-gray-800 dark:text-white font-medium {{ $required_field }}">{{ $field[ 'title' ] }}</label>
    <select 
        name="{{ $field_input_name }}" 
        class="custom-select w-full py-2.5 px-3 bg-white dark:bg-gray-900 border dark:border-gray-800 rounded-md text-sm text-gray-600 dark:text-gray-300 font-normal transition-all hover:border-gray-400 dark:hover:border-gray-400" 
        id="{{ $field[ 'name' ] }}"
	    {{ $required_field }}
	    {{ $multiple_option }}
    >
        <option value=""> Select {{ $field[ 'title' ] }} </option>
        
        @if( $products && count( $products ) > 0 )

            @foreach ( $products as $product )

                @php $selected_val = ( is_array( $section_field_val ) && in_array( $product->id, $section_field_val ) ) ? 'selected' : ''; @endphp

                <option value="{{ $product->id }}" {{ $selected_val }}>{{ '[ ' . $product->sku . ' ]' . $product->name }}</option>

            @endforeach

        @endif

    </select>
</div>

@endif