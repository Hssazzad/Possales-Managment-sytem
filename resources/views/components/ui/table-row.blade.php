{{-- Port of <TableRow>. --}}
<tr {{ $attributes->merge(['class' => 'border-b border-gray-200 hover:bg-gray-50 data-[state=selected]:bg-gray-100']) }} data-slot="table-row">{{ $slot }}</tr>
