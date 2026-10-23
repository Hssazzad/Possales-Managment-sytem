{{-- Port of snippet-table.tsx <Table>. variant: "default" | "card". --}}
@props(['variant' => 'default'])
<div class="w-full overflow-x-auto" data-slot="table-container" data-variant="{{ $variant }}">
    <table
        {{ $attributes->merge(['class' => 'w-full text-sm caption-bottom' . ($variant === 'card' ? ' border-separate border-spacing-0' : '')]) }}
        data-slot="table"
    >
        {{ $slot }}
    </table>
</div>
