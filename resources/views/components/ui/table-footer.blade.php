{{-- Port of <TableFooter>. --}}
<tfoot {{ $attributes->merge(['class' => 'border-t border-gray-200 bg-gray-50 font-medium [&>tr]:last:border-b-0']) }} data-slot="table-footer">{{ $slot }}</tfoot>
