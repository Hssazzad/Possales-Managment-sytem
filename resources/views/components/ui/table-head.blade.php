{{-- Port of <TableHead>. shadcn token text-muted-foreground -> text-gray-500. --}}
<th {{ $attributes->merge(['class' => 'h-10 whitespace-nowrap px-2.5 text-left align-middle font-medium text-gray-500']) }} data-slot="table-head">{{ $slot }}</th>
