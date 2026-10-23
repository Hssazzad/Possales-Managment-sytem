{{-- Port of <TableHeader>. --}}
<thead {{ $attributes->merge(['class' => '[&_tr]:border-b [&_tr]:border-gray-200']) }} data-slot="table-header">{{ $slot }}</thead>
